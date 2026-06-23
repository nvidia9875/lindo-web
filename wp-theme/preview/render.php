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

$artists = require __DIR__ . '/real-data.php';

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
		'contact_form_html' => $contact_form_html,
		'contact_email'     => 'contact@styledbylindo.com',
	)
);
?>
<?php
// プレビュー比較用：Works の「章レイアウト」（既定は非表示。スイッチャーの Feature で切替）。
echo '<div id="works-feature-wrap" hidden>';
lindo_part( 'section-works-feature', array( 'artists' => $artists ) );
echo '</div>';
?>
</main>
<?php lindo_part( 'site-footer', array( 'lindo_year' => gmdate( 'Y' ) ) ); ?>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/loader.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/hero-shatter.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/main.js' ); ?>" defer></script>
<script src="<?php echo esc_url( LINDO_URI . '/assets/js/artist-modal.js' ); ?>" defer></script>

<!-- ▼▼ プレビュー専用：レイアウト/演出の比較スイッチャー（本番テーマ lindo/ には含めない） ▼▼ -->
<div class="pv-switch">
	<div class="pv-row"><span>Works</span>
		<button type="button" data-works="feature" class="is-active">Chapter</button>
		<button type="button" data-works="">Grid</button>
		<button type="button" data-works="dense">Dense</button>
		<button type="button" data-works="index">Index</button>
	</div>
	<div class="pv-row"><span>Hero FX</span>
		<button type="button" data-fx="scatter" class="is-active">Scatter</button>
		<button type="button" data-fx="fall">Fall</button>
		<button type="button" data-fx="repel">Repel</button>
	</div>
	<p class="pv-note">preview only — 本番では1つに確定</p>
</div>
<style>
	.pv-switch {
		position: fixed;
		left: 12px;
		bottom: 12px;
		z-index: 500;
		display: flex;
		flex-direction: column;
		gap: 6px;
		font: 600 11px/1 "Archivo", system-ui, sans-serif;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		background: rgba(38, 36, 15, 0.92);
		color: #fdfbf4;
		padding: 11px 12px;
		border-radius: 3px;
		backdrop-filter: blur(6px);
		box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
	}
	.pv-row { display: flex; align-items: center; gap: 5px; }
	.pv-row > span { width: 62px; color: #ef8e94; }
	.pv-switch button {
		padding: 6px 10px;
		border: 1px solid rgba(253, 251, 244, 0.25);
		color: inherit;
		background: transparent;
		cursor: pointer;
		border-radius: 2px;
		font: inherit;
	}
	.pv-switch button.is-active { background: #ef8e94; color: #26240f; border-color: #ef8e94; }
	.pv-note { margin: 2px 0 0; font-size: 9px; letter-spacing: 0.1em; color: rgba(253, 251, 244, 0.5); text-transform: none; }
	@media (max-width: 560px) { .pv-switch { display: none; } }
</style>
<script>
	(function () {
		var sw = document.querySelector(".pv-switch");
		if (!sw) return;
		function activate(btn) {
			var sib = btn.parentNode.querySelectorAll("button");
			for (var i = 0; i < sib.length; i++) sib[i].classList.remove("is-active");
			btn.classList.add("is-active");
		}
		function applyWorks(m) {
			var cards = document.getElementById("artists");
			var feat = document.getElementById("works-feature-wrap");
			var a = document.querySelector(".artists");
			if (m === "feature") {
				if (cards) cards.hidden = true;
				if (feat) feat.hidden = false;
			} else {
				if (feat) feat.hidden = true;
				if (cards) cards.hidden = false;
				if (a) {
					a.classList.remove("is-dense", "is-index");
					if (m === "dense") a.classList.add("is-dense");
					if (m === "index") a.classList.add("is-index");
				}
			}
		}
		sw.addEventListener("click", function (e) {
			var b = e.target.closest("button");
			if (!b) return;
			if (b.hasAttribute("data-works")) {
				applyWorks(b.getAttribute("data-works"));
				activate(b);
			} else if (b.hasAttribute("data-fx")) {
				if (window.LindoShatter) window.LindoShatter.setMode(b.getAttribute("data-fx"));
				activate(b);
			}
		});
		// プレビューでは章セクションをページ末尾に出力しているので、
		// 比較しやすいよう Works（03）の位置＝カード版の直後へ移動。
		var cardsSec = document.getElementById("artists");
		var featWrap = document.getElementById("works-feature-wrap");
		if (cardsSec && featWrap && cardsSec.parentNode) {
			cardsSec.parentNode.insertBefore(featWrap, cardsSec.nextSibling);
		}

		// 初期状態：アクティブな Works ボタン（既定 = Chapter）を反映。
		var act = sw.querySelector("[data-works].is-active");
		if (act) applyWorks(act.getAttribute("data-works"));
	})();
</script>
<!-- ▲▲ プレビュー専用ここまで ▲▲ -->
</body>
</html>
<?php
echo ob_get_clean();
