<?php
/**
 * スタンドアロン描画（本番の生成物はこれ）。
 *
 * 本番テーマの template-parts をそのまま使い、front-page 相当のHTMLを
 * WordPress 無しで生成する。
 *
 * 実行: php preview/render.php > preview/index.html
 *
 * データ源は2本立て:
 *   作品（artists）  … microcms-data.php / real-data.php
 *   文言（site）     … site-data.php（無ければ inc/site-defaults.php）
 *
 * 【重要】このファイルに文言を直書きしないこと。直書きすると先方が自分で直せず、
 * 「ヒーローの一行を変えたい」だけで開発者への依頼が発生する。文言は必ず
 * inc/site-defaults.php ＋ microCMS の site API を経由させる。
 *
 * @package LINDO\Preview
 */

define( 'LINDO_DIR', dirname( __DIR__ ) . '/lindo' );
define( 'LINDO_URI', '../lindo' ); // index.html は preview/ 配下に置く前提。
define( 'LINDO_VERSION', 'preview' );

require __DIR__ . '/wp-shim.php';
require LINDO_DIR . '/inc/template.php';

// データ源の切替（移行期間中の両立）。
//   MICROCMS_API_KEY あり → microCMS（新・本命）
//   MICROCMS_FIXTURE あり → ローカルJSON（開発用。APIを叩かず異常系を再現する）
//   どちらも無し          → works-img/ をローカル走査（旧・従来どおり）
// 全アーティストの microCMS 投入が終わったら real-data.php ごと廃止する。
$artists = ( getenv( 'MICROCMS_API_KEY' ) || getenv( 'MICROCMS_FIXTURE' ) )
	? require __DIR__ . '/microcms-data.php'
	: require __DIR__ . '/real-data.php';

// サイト文言。artists の取得（＝キーの検証）が通ったあとに読む。
$site = require __DIR__ . '/site-data.php';

// フォールバックフォームを部品から取得（本番と同じマークアップ）。
ob_start();
lindo_part( 'contact-form-fallback' );
$contact_form_html = (string) ob_get_clean();

ob_start();
?>
<!doctype html>
<html lang="ja">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php if ( ! empty( $site['noindex'] ) ) : ?>
		<meta name="robots" content="noindex,nofollow" />
	<?php endif; ?>
	<meta name="theme-color" content="#eae5d7" />
	<title><?php echo esc_html( $site['title'] ); ?></title>
	<meta name="description" content="<?php echo esc_attr( $site['description'] ); ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?php echo esc_attr( $site['title'] ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( $site['description'] ); ?>" />
	<?php if ( ! empty( $site['ogImage'] ) ) : ?>
		<meta property="og:image" content="<?php echo esc_url( $site['ogImage'] ); ?>" />
		<meta name="twitter:card" content="summary_large_image" />
	<?php else : ?>
		<meta name="twitter:card" content="summary" />
	<?php endif; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="<?php echo esc_url( LINDO_URI . '/assets/css/lindo.css' ); ?>" />
</head>
<body>
<?php lindo_part( 'site-loader', array( 'loader_sub' => $site['loaderSub'] ) ); ?>
<?php
lindo_part(
	'site-header',
	array(
		'lindo_nav_base' => '',
		// ナビの表示名は各セクションの見出しと同じ値を使う（別々に持たない）。
		'nav_labels'     => array(
			'about'   => $site['about']['label'],
			'service' => $site['service']['label'],
			'works'   => $site['works']['label'],
			'contact' => $site['contact']['label'],
		),
	)
);
?>
<main id="main">
<?php
lindo_part(
	'front-sections',
	array(
		'artists'           => $artists,
		'site'              => $site,
		'contact_form_html' => $contact_form_html,
	)
);
?>
</main>
<?php
lindo_part(
	'site-footer',
	array(
		'lindo_year' => gmdate( 'Y' ),
		'company'    => $site['company'],
		'contact'    => $site['contact'],
	)
);
?>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/loader.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/hero-fx.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/main.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/artist-modal.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/lightbox.js' ); ?>" defer></script>
</body>
</html>
<?php
echo ob_get_clean();
