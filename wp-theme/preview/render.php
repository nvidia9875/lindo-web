<?php
/**
 * スタンドアロン・プレビュー描画。
 *
 * 本番テーマの template-parts をそのまま使い、front-page 相当のHTMLを
 * WordPress 無しで生成する（デザイン確認・スクショ検証用）。
 *
 * 実行: php preview/render.php > preview/index.html
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

// 本番は Customizer（inc/contact.php・inc/partners.php）から取るが、
// プレビューは WordPress 無しで動くため既定値をここに持つ。
// ※ inc/partners.php の lindo_default_partners() と並びを合わせること。
$contact_email = 'contact@styledbylindo.com';
$partners      = array(
	'avex',
	'universal music',
	'sony music',
	'HYBE JAPAN',
	'LDH JAPAN',
	'BMSG',
	'吉本興業',
	'TWIN PLANET',
	'ホリプロ',
	'VANTAN',
);

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
	<meta name="robots" content="noindex,nofollow" />
	<meta name="theme-color" content="#eae5d7" />
	<title>LINDO — Preview（No4 / Artists + Contact）</title>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="<?php echo esc_url( LINDO_URI . '/assets/css/lindo.css' ); ?>" />
</head>
<body>
<?php lindo_part( 'site-loader' ); ?>
<?php lindo_part( 'site-header', array( 'lindo_nav_base' => '' ) ); ?>
<main id="main">
<?php
lindo_part(
	'front-sections',
	array(
		'artists'           => $artists,
		'representative'    => array(
			'name'    => 'MAI ITO',
			'title'   => '代表取締役 / CEO',
			'profile' => array(
				'文化女子大学卒業後、株式会社LDH apparelにて衣装デザイナー兼ディレクターを担当。2019年にフリーランスへ転向。',
				'韓国事務所主催のオーディションプログラムにてスタイルディレクターとして1年間渡韓。帰国後、韓国・日本のアーティストのスタイルディレクション及びビジュアルプロデュースを手がける。',
				'2024年、アーティストのビジュアル作りに特化した撮影の企画／制作をトータルプロデュースする株式会社LINDOを設立。',
			),
		),
		'partners'          => $partners,
		'contact_form_html' => $contact_form_html,
		'contact_email'     => $contact_email,
	)
);
?>
</main>
<?php
lindo_part(
	'site-footer',
	array(
		'lindo_year'    => gmdate( 'Y' ),
		'contact_email' => $contact_email,
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
