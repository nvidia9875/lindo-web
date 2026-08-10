<?php
/**
 * イントロローダー（最初に約2.2秒表示）。
 * 視覚はCSS駆動（JS無効でも自動で消える）。JSはカウンターとスクロールロックの上乗せ。
 *
 * 期待する変数:
 *   $loader_sub  string  ロゴ下の小さい文字
 *
 * ロゴ文字（LINDO）は直書きのまま。ロゴSVGの支給時にまとめて差し替える前提で、
 * 中途半端に編集可能にしても混乱するだけのため。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$loader_sub = isset( $loader_sub ) ? (string) $loader_sub : '';
?>
<div class="loader" data-loader aria-hidden="true">
	<div class="loader-inner">
		<span class="loader-mark">LIND<b>O</b></span>
		<span class="loader-sub"><?php echo esc_html( $loader_sub ); ?></span>
	</div>
	<span class="loader-count"><span data-loader-num>0</span><span class="pk">%</span></span>
	<span class="loader-bar"><span></span></span>
</div>
