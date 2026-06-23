<?php
/**
 * イントロローダー（最初に約2.2秒表示）。
 * 視覚はCSS駆動（JS無効でも自動で消える）。JSはカウンターとスクロールロックの上乗せ。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
?>
<div class="loader" data-loader aria-hidden="true">
	<div class="loader-inner">
		<span class="loader-mark">LIND<b>O</b></span>
		<span class="loader-sub">Visual Creative</span>
	</div>
	<span class="loader-count"><span data-loader-num>0</span><span class="pk">%</span></span>
	<span class="loader-bar"><span></span></span>
</div>
