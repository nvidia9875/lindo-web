<?php
/**
 * microCMS の「アーティスト」へ、10組・27作品・332枚を一括投入する。
 *
 * content-manifest.php と works-img/ を唯一の情報源として、
 *   1) 画像を microCMS のメディアへアップロード
 *   2) 作品ごとに画像URLをまとめてコンテンツを作成
 * まで自動で行う。管理画面での手作業は「公開」を押すだけになる。
 *
 * 【重要】APIで作ったコンテンツは**下書き**状態で入る。
 * 公開は管理画面から行う（意図しない公開を防ぐため、あえて自動化していない）。
 *
 * 必要な権限:
 *   マネジメントAPI … メディアの取得 / メディアのアップロード
 *   コンテンツAPI …… artists の POST
 *
 * 実行:
 *   set -a; . ./.env.local; set +a
 *   php scripts/push-artists.php            # 確認のみ（何も送らない）
 *   php scripts/push-artists.php --write    # 実行
 *
 * 途中で失敗しても、アップロード済みのメディアは再利用される
 * （同名ファイルは再アップロードせず既存URLを使う）ので、そのまま再実行してよい。
 *
 * @package LINDO\Scripts
 */

define( 'BASE', __DIR__ . '/../wp-theme/preview' );

$manifest = require BASE . '/content-manifest.php';
$service  = getenv( 'MICROCMS_SERVICE_ID' ) ?: 'lindo-web';
$key      = getenv( 'MICROCMS_API_KEY' );
$write    = in_array( '--write', $argv, true );

if ( '' === (string) $key ) {
	fwrite( STDERR, "MICROCMS_API_KEY が未設定です。\n" );
	exit( 1 );
}

/** microCMS へHTTPリクエスト。 */
function mc( $method, $url, $opts = array() ) {
	global $key;
	$ch      = curl_init( $url );
	$headers = array( 'X-MICROCMS-API-KEY: ' . $key );
	$setopt  = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST  => $method,
		CURLOPT_TIMEOUT        => 120,
	);
	if ( isset( $opts['json'] ) ) {
		$headers[]                  = 'Content-Type: application/json';
		$setopt[ CURLOPT_POSTFIELDS ] = json_encode( $opts['json'], JSON_UNESCAPED_UNICODE );
	}
	if ( isset( $opts['file'] ) ) {
		$setopt[ CURLOPT_POSTFIELDS ] = array( 'file' => $opts['file'] );
	}
	$setopt[ CURLOPT_HTTPHEADER ] = $headers;
	curl_setopt_array( $ch, $setopt );
	$body   = curl_exec( $ch );
	$status = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	unset( $ch );
	return array( $status, json_decode( (string) $body, true ), (string) $body );
}

/** 既存メディアを「ファイル名 => URL」で引けるようにする（再実行時の重複防止）。 */
function fetch_media_index( $service ) {
	$index  = array();
	$offset = 0;
	do {
		list( $st, $j ) = mc( 'GET', sprintf( 'https://%s.microcms-management.io/api/v1/media?limit=100&offset=%d', $service, $offset ) );
		if ( 200 !== $st ) {
			break;
		}
		foreach ( (array) ( $j['media'] ?? array() ) as $m ) {
			// URL のパスは percent-encode されている（例: スペース → %20）。
			// アップロード時のファイル名と突き合わせるためデコードする。
			// これを忘れると名前にスペースを含む作品だけ既存判定が効かず、
			// 再実行のたびに重複アップロードが起きる。
			$index[ rawurldecode( basename( parse_url( $m['url'], PHP_URL_PATH ) ) ) ] = $m['url'];
		}
		$offset += 100;
		$total   = (int) ( $j['totalCount'] ?? 0 );
	} while ( $offset < $total );
	return $index;
}

// ── 投入する内容を組み立てる ───────────────────────────
$plan  = array();
$order = 0;
foreach ( $manifest['order'] as $folder ) {
	$a = $manifest['artists'][ $folder ] ?? null;
	if ( ! $a ) {
		continue;
	}
	$order++;
	$works = array();
	foreach ( $a['works'] as $w ) {
		$dir   = BASE . '/works-img/' . $folder . '/' . $w['key'];
		$files = array();
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $f ) {
				if ( preg_match( '/\.webp$/i', $f ) ) {
					$files[] = $dir . '/' . $f;
				}
			}
			sort( $files ); // 01.webp, 02.webp… の順＝先方指定の並び順
		}
		$works[] = array(
			'key'   => $w['key'],
			'title' => $w['title'],
			'role'  => $w['role'],
			'url'   => $w['url'] ?? '',
			'files' => $files,
			// メディア上のファイル名。作品をまたいで 01.webp が衝突するので接頭辞を付ける。
			'names' => array_map(
				function ( $f ) use ( $folder, $w ) {
					return $folder . '__' . $w['key'] . '__' . basename( $f );
				},
				$files
			),
		);
	}
	$plan[] = array(
		'folder' => $folder,
		'name'   => $a['name'],
		'role'   => $a['role'],
		'order'  => $order,
		'works'  => $works,
	);
}

$total_imgs = array_sum( array_map( fn( $p ) => array_sum( array_map( fn( $w ) => count( $w['files'] ), $p['works'] ) ), $plan ) );
printf( "サービス: %s\n投入予定: %d組 / %d作品 / %d枚\n\n", $service, count( $plan ), array_sum( array_map( fn( $p ) => count( $p['works'] ), $plan ) ), $total_imgs );
foreach ( $plan as $p ) {
	printf( "  %2d. %-22s %d作品 %3d枚\n", $p['order'], $p['name'], count( $p['works'] ), array_sum( array_map( fn( $w ) => count( $w['files'] ), $p['works'] ) ) );
}

if ( ! $write ) {
	echo "\n（確認のみ。実行するには --write を付けてください）\n";
	exit( 0 );
}

// ── 実行 ───────────────────────────────────────────
echo "\n既存メディアを確認中…\n";
$media = fetch_media_index( $service );
printf( "  既存 %d件\n\n", count( $media ) );

$uploaded = 0;
$reused   = 0;
foreach ( $plan as &$p ) {
	printf( "▼ %s\n", $p['name'] );
	foreach ( $p['works'] as &$w ) {
		$urls = array();
		foreach ( $w['files'] as $i => $file ) {
			$name = $w['names'][ $i ];
			if ( isset( $media[ $name ] ) ) {
				$urls[] = $media[ $name ];
				$reused++;
				continue;
			}
			// アップロードはレート制限（429）に当たるので、待って再試行する。
			// 待ち時間を倍にしていく（1秒 → 2 → 4 …）。
			$j = null;
			for ( $try = 0; $try < 7; $try++ ) {
				$cf                   = new CURLFile( $file, 'image/webp', $name );
				list( $st, $j, $raw ) = mc( 'POST', sprintf( 'https://%s.microcms-management.io/api/v1/media', $service ), array( 'file' => $cf ) );
				if ( 201 === $st && ! empty( $j['url'] ) ) {
					break;
				}
				if ( 429 !== $st ) {
					fwrite( STDERR, "  [失敗] {$name}: HTTP {$st} {$raw}\n" );
					exit( 1 );
				}
				$wait = 1 << $try;
				printf( "    429のため %d秒待機して再試行（%s）\n", $wait, $name );
				sleep( $wait );
			}
			if ( empty( $j['url'] ) ) {
				fwrite( STDERR, "  [失敗] {$name}: 再試行しても429が続きました\n" );
				exit( 1 );
			}
			// 平常時も少し間隔を空けて429を避ける。
			usleep( 350000 );
			$media[ $name ] = $j['url'];
			$urls[]         = $j['url'];
			$uploaded++;
			if ( 0 === $uploaded % 20 ) {
				printf( "    …%d枚アップロード済み\n", $uploaded );
			}
		}
		$w['urls'] = $urls;
		printf( "  %-26s %3d枚\n", $w['key'], count( $urls ) );
	}
	unset( $w );
}
unset( $p );

printf( "\nアップロード: 新規 %d枚 / 既存再利用 %d枚\n\nコンテンツを作成中…\n", $uploaded, $reused );

foreach ( $plan as $p ) {
	$works = array();
	foreach ( $p['works'] as $w ) {
		$item = array(
			'fieldId' => 'work',
			'title'   => $w['title'],
			'role'    => $w['role'],
			// gallery（複数画像）は **URLの文字列配列**。
			// array( 'url' => ... ) の形で送ると 'works' has unexpected data type. になる。
			'gallery' => $w['urls'],
		);
		if ( '' !== $w['url'] ) {
			$item['url'] = $w['url'];
		}
		$works[] = $item;
	}
	$body = array(
		'name'  => $p['name'],
		'role'  => $p['role'],
		'order' => $p['order'],
		'works' => $works,
	);
	list( $st, $j, $raw ) = mc( 'POST', sprintf( 'https://%s.microcms.io/api/v1/artists', $service ), array( 'json' => $body ) );
	if ( $st < 200 || $st >= 300 ) {
		fwrite( STDERR, "  [失敗] {$p['name']}: HTTP {$st} {$raw}\n" );
		exit( 1 );
	}
	printf( "  ✓ %-22s id=%s\n", $p['name'], $j['id'] ?? '?' );
}

echo "\n完了。**管理画面で各アーティストを「公開」してください**（APIで作ると下書き状態のため）。\n";
