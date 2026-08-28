<?php
/**
 * 04 Artists（microCMS の `talents` API）へ初期データを投入する。
 *
 * preview/talents-seed.php と preview/talents-img/ を情報源として、
 *   1) 写真を microCMS のメディアへアップロード
 *   2) その画像URLでコンテンツを作成
 * まで行う。管理画面での手作業は「公開」を押すだけ。
 *
 * 【前提】microCMS 側に `talents` API が作成済みであること。
 * APIの作成だけは管理画面からしかできない（作成用のAPIが提供されていない）。
 * microcms-schema/README.md の手順に従って、api-talents.json をインポートする。
 *
 * 【重要】APIで作ったコンテンツは**下書き**で入る。公開は管理画面から。
 * push-artists.php と同じ方針（意図しない公開を防ぐため、あえて自動化しない）。
 *
 * 必要な権限:
 *   マネジメントAPI … メディアの取得 / メディアのアップロード
 *   コンテンツAPI …… talents の POST
 *
 * 実行:
 *   set -a; . ./.env.local; set +a
 *   php scripts/push-talents.php            # 確認のみ（何も送らない）
 *   php scripts/push-talents.php --write    # 実行
 *
 * 同名ファイルは再アップロードしないので、途中で失敗しても再実行してよい。
 *
 * @package LINDO\Scripts
 */

define( 'BASE', __DIR__ . '/../wp-theme/preview' );

$seed    = require BASE . '/talents-seed.php';
$service = getenv( 'MICROCMS_SERVICE_ID' ) ?: 'lindo-web';
$key     = getenv( 'MICROCMS_API_KEY' );
$write   = in_array( '--write', $argv, true );

if ( '' === (string) $key ) {
	fwrite( STDERR, "MICROCMS_API_KEY が未設定です（set -a; . ./.env.local; set +a）。\n" );
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
		$headers[]                    = 'Content-Type: application/json';
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
	$total  = 0;
	do {
		list( $st, $j ) = mc( 'GET', sprintf( 'https://%s.microcms-management.io/api/v1/media?limit=100&offset=%d', $service, $offset ) );
		if ( 200 !== $st ) {
			break;
		}
		foreach ( (array) ( $j['media'] ?? array() ) as $m ) {
			// URL のパスは percent-encode されている。ファイル名と突き合わせるためデコードする。
			$index[ rawurldecode( basename( parse_url( $m['url'], PHP_URL_PATH ) ) ) ] = $m['url'];
		}
		$offset += 100;
		$total   = (int) ( $j['totalCount'] ?? 0 );
	} while ( $offset < $total );
	return $index;
}

// ── 投入する内容を組み立てる ───────────────────────────
$plan = array();
foreach ( $seed as $i => $t ) {
	$file = BASE . '/' . ( $t['image']['url'] ?? '' );
	if ( ! is_file( $file ) ) {
		fwrite( STDERR, sprintf( "写真が見つかりません: %s（%s）\n", $file, $t['name'] ) );
		exit( 1 );
	}
	$plan[] = array(
		'name'    => $t['name'],
		'nameSub' => (string) ( $t['name_sub'] ?? '' ),
		'url'     => (string) ( $t['url'] ?? '' ),
		'order'   => $i + 1,
		'file'    => $file,
		// 03 Works のメディアと混ざらないよう接頭辞を付ける。
		'as'      => 'talents__' . basename( $file ),
	);
}

echo "サービス: {$service}\n";
echo '投入対象: ' . count( $plan ) . "組\n\n";
foreach ( $plan as $p ) {
	printf( "  %d. %-14s %s  ← %s\n", $p['order'], $p['name'], $p['url'], basename( $p['file'] ) );
}
echo "\n";

if ( ! $write ) {
	echo "確認のみ（--write を付けると実際に投入します）。\n";
	exit( 0 );
}

// API の存在確認。無いまま POST すると 404 の意味が分かりにくいので先に見る。
list( $st ) = mc( 'GET', sprintf( 'https://%s.microcms.io/api/v1/talents?limit=1', $service ) );
if ( 404 === $st ) {
	fwrite( STDERR, "microCMS に talents API がありません。先に microcms-schema/README.md の手順で作成してください。\n" );
	exit( 1 );
}

echo "既存メディアを確認中...\n";
$media = fetch_media_index( $service );
echo '  ' . count( $media ) . "件\n\n";

foreach ( $plan as $p ) {
	// 1) 画像
	if ( isset( $media[ $p['as'] ] ) ) {
		$img_url = $media[ $p['as'] ];
		echo "  写真: 既存を再利用（{$p['as']}）\n";
	} else {
		$cf = new CURLFile( $p['file'], mime_content_type( $p['file'] ), $p['as'] );
		list( $st, $j, $raw ) = mc( 'POST', sprintf( 'https://%s.microcms-management.io/api/v1/media', $service ), array( 'file' => $cf ) );
		if ( 201 !== $st && 200 !== $st ) {
			fwrite( STDERR, "写真のアップロードに失敗（HTTP {$st}）: {$raw}\n" );
			exit( 1 );
		}
		$img_url = $j['url'] ?? '';
		echo "  写真: アップロード完了（{$p['as']}）\n";
	}

	// 2) コンテンツ
	$body = array(
		'name'    => $p['name'],
		'nameSub' => $p['nameSub'],
		'url'     => $p['url'],
		'order'   => $p['order'],
		'image'   => $img_url,
	);
	list( $st, $j, $raw ) = mc( 'POST', sprintf( 'https://%s.microcms.io/api/v1/talents', $service ), array( 'json' => $body ) );
	if ( 201 !== $st ) {
		fwrite( STDERR, "コンテンツ作成に失敗（HTTP {$st}）: {$raw}\n" );
		exit( 1 );
	}
	echo "  作成: {$p['name']}（下書き）\n\n";
}

echo "完了。microCMS の管理画面で内容を確認し、「公開」を押してください。\n";
