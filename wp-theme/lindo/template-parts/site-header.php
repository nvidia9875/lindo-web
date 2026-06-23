<?php
/**
 * サイトヘッダー（固定ナビ）。
 * 1ページ構成のためアンカーナビ。WP/プレビュー共用。
 *
 * 期待する変数:
 *   $lindo_nav_base  アンカーの前置き。フロントページ/プレビューは ''（＝ #about のまま
 *                    同一ページ内スクロール）。サブページからは home_url('/') を渡し、
 *                    トップへ遷移してから該当箇所へ。
 *   $lindo_brand     ブランド表記（既定 'LIND<b>O</b>'）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_nav_base = isset( $lindo_nav_base ) ? $lindo_nav_base : '';
$lindo_brand    = isset( $lindo_brand ) ? $lindo_brand : 'LIND<b>O</b>';

// フロント上ではロゴ＝最上部へ、サブページ上ではトップURLへ。
$lindo_logo_href = '' !== $lindo_nav_base ? $lindo_nav_base : '#main';

$lindo_nav = array(
	'#about'   => 'About',
	'#service' => 'What We Do',
	'#artists' => 'Works',
	'#contact' => 'Contact',
);
?>
<a class="skip" href="#main">本文へスキップ</a>
<div class="scroll-progress" aria-hidden="true"></div>
<header class="hd" data-header>
	<div class="wrap hd-in">
		<a class="logo" href="<?php echo esc_url( $lindo_logo_href ); ?>" aria-label="LINDO"><?php echo wp_kses( $lindo_brand, array( 'b' => array() ) ); ?></a>
		<nav class="nav" data-nav aria-label="メインナビゲーション">
			<?php foreach ( $lindo_nav as $anchor => $label ) : ?>
				<a href="<?php echo esc_url( $lindo_nav_base . $anchor ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<button class="nav-toggle" data-nav-toggle aria-label="メニュー" aria-expanded="false"><span></span><span></span><span></span></button>
	</div>
</header>
