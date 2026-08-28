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
 *   Artists（04）    … talents-data.php（無ければ talents-seed.php）
 *   文言（site）     … site-data.php（無ければ inc/site-defaults.php）
 *
 * 【重要】このファイルに文言を直書きしないこと。直書きすると先方が自分で直せず、
 * 「ヒーローの一行を変えたい」だけで開発者への依頼が発生する。文言は必ず
 * inc/site-defaults.php ＋ microCMS の site API を経由させる。
 *
 * @package LINDO\Preview
 */

define( 'LINDO_DIR', dirname( __DIR__ ) . '/lindo' );
// 生成した index.html から見たアセットの位置。dist/ に index.html と assets/ を
// 並べて置く（scripts/build-site.sh）ので相対で './assets/...' になる。
//
// 絶対パス（'/assets'）にしてはいけない。GitHub Pages のプロジェクトページは
// /lindo-web/ 配下に出るため、/assets/... はドメイン直下を指して404になる。
// 相対にしておけば、プロジェクトページでも独自ドメイン直下でも同じHTMLが動く。
define( 'LINDO_URI', '.' );
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

// 04 Artists（公式サイトへのリンク一覧）。03 Works とは別のAPI（talents）。
// API が未作成なら初期値で描画されるので、ここで落ちることはない。
$talents = require __DIR__ . '/talents-data.php';

// サイト文言。artists の取得（＝キーの検証）が通ったあとに読む。
$site = require __DIR__ . '/site-data.php';

// 公開先URL。canonical / og:url / フォームの送信後遷移先の基準。末尾スラッシュ無し。
$site_url = isset( $site['siteUrl'] ) ? rtrim( (string) $site['siteUrl'], '/' ) : '';

// お問い合わせの中身（Contact セクションの右カラム）。
//
// **アクセスキーの有無で自動的に切り替わる。**
//   WEB3FORMS_ACCESS_KEY あり → フォーム（実際に送信される）
//   なし                     → メール導線
//
// こうしてあるのは「見た目だけのフォーム」を構造的に作れなくするため。
// 押しても何も起きないフォームは、問い合わせを丸ごと取りこぼす。
//
// 有効化・無効化はコードを触らずに環境変数だけでできる:
//   gh variable set WEB3FORMS_ACCESS_KEY --repo nvidia9875/lindo-web --body "<キー>"
//   gh variable delete WEB3FORMS_ACCESS_KEY --repo nvidia9875/lindo-web   ← メール導線に戻る
//
// ※ アクセスキーは生成HTMLに出るので秘密ではない（Web3Forms の仕様上そういうもの）。
//   Secret ではなく Variable でよい。悪用されたら管理画面で再発行する。
$lindo_form_key = trim( (string) getenv( 'WEB3FORMS_ACCESS_KEY' ) );

ob_start();
if ( '' !== $lindo_form_key ) {
	lindo_part(
		'contact-form',
		array(
			'contact'       => $site['contact'],
			'form_key'      => $lindo_form_key,
			// 送信後の遷移先。Web3Forms は絶対URLを要求する。
			'form_redirect' => '' !== $site_url ? $site_url . '/thanks.html' : '',
		)
	);
} else {
	lindo_part( 'contact-mail', array( 'contact' => $site['contact'] ) );
}
$contact_body_html = (string) ob_get_clean();

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
	<meta name="theme-color" content="#fdfbf4" />
	<title><?php echo esc_html( $site['title'] ); ?></title>
	<meta name="description" content="<?php echo esc_attr( $site['description'] ); ?>" />
	<?php
	// 1ページ構成なので canonical は常にトップ。GitHub Pages のプロジェクトURLと
	// 独自ドメインの2つでアクセスできてしまうため、正規のURLを明示する。
	if ( '' !== $site_url ) :
		?>
		<link rel="canonical" href="<?php echo esc_url( $site_url . '/' ); ?>" />
		<meta property="og:url" content="<?php echo esc_url( $site_url . '/' ); ?>" />
	<?php endif; ?>
	<link rel="icon" href="<?php echo esc_url( LINDO_URI . '/favicon.svg' ); ?>" type="image/svg+xml" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?php echo esc_attr( $site['title'] ); ?>" />
	<meta property="og:description" content="<?php echo esc_attr( $site['description'] ); ?>" />
	<meta property="og:site_name" content="<?php echo esc_attr( $site['company']['name'] ); ?>" />
	<meta property="og:locale" content="ja_JP" />
	<?php
	// og:image は microCMS「サイト設定」の SNS共有画像が最優先。未設定でも
	// リポジトリ同梱の ogp.png（1200x630）に必ず落ちるので、共有カードが
	// 画像なしになることは無い。og:image は絶対URLでないと無視される。
	$og_image = ! empty( $site['ogImage'] )
		? (string) $site['ogImage']
		: ( '' !== $site_url ? $site_url . '/ogp.png' : '' );
	if ( '' !== $og_image ) :
		?>
		<meta property="og:image" content="<?php echo esc_url( $og_image ); ?>" />
		<meta property="og:image:width" content="1200" />
		<meta property="og:image:height" content="630" />
		<meta property="og:image:alt" content="<?php echo esc_attr( $site['title'] ); ?>" />
		<meta name="twitter:card" content="summary_large_image" />
	<?php else : ?>
		<meta name="twitter:card" content="summary" />
	<?php endif; ?>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="<?php echo esc_url( LINDO_URI . '/assets/css/lindo.css' ); ?>" />
	<?php
	// 構造化データ（Organization）。社名「株式会社LINDO」とこのドメインを
	// 検索側に結び付ける。見た目には影響しない。
	lindo_part(
		'site-jsonld',
		array(
			'site'     => $site,
			'site_url' => $site_url,
		)
	);
	?>
</head>
<body>
<?php lindo_part( 'site-logo-defs' ); ?>
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
		'talents'           => $talents,
		'site'              => $site,
		'contact_body_html' => $contact_body_html,
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
