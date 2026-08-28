<?php
/**
 * microCMS からアーティストデータを取得して描画用配列にする。
 *
 * real-data.php（ローカル works-img を走査する旧方式）と **まったく同じ形** を返す。
 * これが唯一かつ最重要の要件で、これさえ守れば template-parts / CSS / JS は無改修で動く。
 * 形の定義は MICROCMS-SCHEMA.md「1. 守るべき契約」を参照。
 *
 * 必要な環境変数:
 *   MICROCMS_API_KEY     取得用APIキー（必須）
 *   MICROCMS_SERVICE_ID  サービスID（既定 'lindo'）
 *
 * 実行例（キーはリポジトリに置かない。.env.local は .gitignore 済）:
 *   set -a; . ./.env.local; set +a; php preview/render.php > preview/index.html
 *
 * @package LINDO\Preview
 */

require_once __DIR__ . '/microcms-client.php';

require_once __DIR__ . '/microcms-img.php';

/**
 * microCMS API を叩いて contents を返す。
 *
 * 失敗は握りつぶさず必ず例外にする。ここで空配列を返すと「アーティスト0人の
 * 正常なページ」がビルドされて本番に出てしまい、事故に気づけないため。
 *
 * @throws RuntimeException 設定不備・通信失敗・APIエラー時。
 * @return array<int,array>
 */
function lindo_mc_fetch_artists() {
	// 開発用の抜け道: APIを叩かずローカルのJSONで動かす。
	// 異常系（重複検知など。正常時は沈黙するので壊れても気づけない）の確認に使う。
	$fixture = (string) getenv( 'MICROCMS_FIXTURE' );
	if ( '' !== $fixture ) {
		if ( ! is_readable( $fixture ) ) {
			throw new RuntimeException( 'MICROCMS_FIXTURE のファイルが読めません: ' . $fixture );
		}
		$json = json_decode( (string) file_get_contents( $fixture ), true );
		if ( ! is_array( $json ) || ! isset( $json['contents'] ) ) {
			throw new RuntimeException( 'MICROCMS_FIXTURE の JSON に contents がありません。' );
		}
		return $json['contents'];
	}

	// limit の既定は 10。アーティストは9組だが将来増えるので余裕を持たせる。
	// orders=order で表示順（数字フィールド）に並べる。
	list( $status, $body ) = lindo_mc_request(
		'artists',
		array(
			'limit'  => 100,
			'orders' => 'order',
		)
	);

	if ( 200 !== $status ) {
		throw new RuntimeException( 'microCMS が HTTP ' . $status . ' を返しました。APIキー／サービスID／エンドポイント名を確認してください。' );
	}

	$json = json_decode( (string) $body, true );
	if ( ! is_array( $json ) || ! isset( $json['contents'] ) || ! is_array( $json['contents'] ) ) {
		throw new RuntimeException( 'microCMS のレスポンスを解釈できませんでした（contents が見つかりません）。' );
	}
	if ( empty( $json['contents'] ) ) {
		throw new RuntimeException( 'microCMS にアーティストが1件もありません。下書きのままになっていないか（公開済みか）確認してください。' );
	}

	return $json['contents'];
}

/**
 * 空行区切りのテキスト → 段落配列。
 *
 * 分割規則はサイト全体で1本にする（inc/template.php）。ここで独自に持つと、
 * プロフィールだけ改行の扱いが違う、が起きる。
 *
 * @param string $raw テキスト。
 * @return array<int,string>
 */
function lindo_mc_paragraphs( $raw ) {
	return lindo_split_paras( $raw );
}

/**
 * role → tags。本番 inc/artist-data.php:36 と同じ分割規則を踏襲。
 *
 * @param string $role 担当。
 * @return array<int,string>
 */
function lindo_mc_tags( $role ) {
	$parts = preg_split( '/[\/、,]+/u', (string) $role );
	$tags  = array();
	foreach ( $parts as $p ) {
		$p = trim( $p );
		if ( '' !== $p ) {
			$tags[] = $p;
		}
	}
	return $tags;
}

$contents = lindo_mc_fetch_artists();

$artists = array();
$index   = 0;

foreach ( $contents as $c ) {
	$name = isset( $c['name'] ) ? trim( (string) $c['name'] ) : '';
	if ( '' === $name ) {
		continue;
	}

	$role      = isset( $c['role'] ) ? (string) $c['role'] : '';
	$raw_works = isset( $c['works'] ) && is_array( $c['works'] ) ? $c['works'] : array();

	$works = array();
	foreach ( $raw_works as $wi => $w ) {
		// 未設定の任意フィールドは **キーごと返ってこない**（空文字ではない）。必ず isset で見る。
		$gallery_raw = isset( $w['gallery'] ) && is_array( $w['gallery'] ) ? $w['gallery'] : array();
		if ( empty( $gallery_raw ) ) {
			continue; // 写真が無い作品は出せない（cover が作れないため）。
		}

		$title = isset( $w['title'] ) ? (string) $w['title'] : '';
		$alt   = trim( $name . ' ' . $title );

		$gallery = array();
		$seen    = array();
		foreach ( $gallery_raw as $img ) {
			if ( ! is_array( $img ) || ! isset( $img['url'] ) ) {
				continue;
			}
			// 同じ画像を2度入れてしまう事故の検知。実際に発生した（SugarNote のピンク背景で
			// 05.webp が2回入り11枚のはずが12枚になっていた）。管理画面で複数選択＋ドラッグを
			// する以上、必ず再発する。重複は常に間違いなので気づけるようにする。
			// ※ stdout は HTML なので、絶対に stderr へ出すこと。
			if ( isset( $seen[ $img['url'] ] ) ) {
				lindo_mc_warn(
					sprintf(
						'画像の重複: %s / %s に同じ画像が2回入っています（%s）。管理画面で片方を削除してください。',
						$name,
						'' !== $title ? $title : '(無題の作品)',
						basename( (string) strtok( (string) $img['url'], '?' ) )
					)
				);
				continue; // 重複は落として1枚だけ採用する。
			}
			$seen[ $img['url'] ] = true;
			$gallery[]           = lindo_mc_img( $img, $alt );
		}
		if ( empty( $gallery ) ) {
			continue;
		}

		$works[] = array(
			'slug'    => 'work-' . ( $index + 1 ) . '-' . ( (int) $wi + 1 ),
			'title'   => $title,
			'role'    => isset( $w['role'] ) ? (string) $w['role'] : '',
			// url があると artist-modal が「▶付きの外部リンクタイル」として描く（MV等）。
			'url'     => isset( $w['url'] ) ? (string) $w['url'] : '',
			'cover'   => $gallery[0],
			'gallery' => $gallery,
		);
	}

	if ( empty( $works ) ) {
		continue; // 表示できる作品が無いアーティストは出さない（real-data.php と同じ挙動）。
	}
	$index++;

	$artists[] = array(
		'id'       => $index,
		'slug'     => 'artist-' . $index,
		'index'    => sprintf( '%02d', $index ),
		'name'     => $name,
		'name_sub' => isset( $c['nameSub'] ) ? (string) $c['nameSub'] : '',
		'role'     => $role,
		'tags'     => lindo_mc_tags( $role ),
		'profile'  => isset( $c['profile'] ) ? lindo_mc_paragraphs( $c['profile'] ) : array(),
		'portrait' => $works[0]['cover'],
		'gallery'  => array_map(
			function ( $wk ) {
				return $wk['cover'];
			},
			$works
		),
		'works'    => $works,
		'links'    => array(), // microCMS 側に links を作っていないため常に空（現状どのアーティストも未使用）。
	);
}

if ( empty( $artists ) ) {
	throw new RuntimeException( '描画できるアーティストが0件でした（全件が works 未設定の可能性）。' );
}

return $artists;
