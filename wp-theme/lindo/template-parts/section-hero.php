<?php
/**
 * ヒーロー。
 *
 * 期待する変数:
 *   $hero  array{label,labelStrong,meta,line1,line2,lead,tags}（site-defaults.php 由来）
 *
 * 見出しは2行固定。行ごとに散らばるアニメーション（assets/js/hero-fx.js）が
 * `.ln > .ln-text[data-line]` の構造を前提にしているため、行数は増やせない。
 * 末尾のピンクの「.」は装飾なのでCMSには入力させず、ここで付ける。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$hero = isset( $hero ) && is_array( $hero ) ? $hero : array();
?>
<section class="hero">
	<div class="wrap">
		<div class="hero-top">
			<p class="lbl"><?php echo lindo_lines( isset( $hero['label'] ) ? $hero['label'] : '' ); ?><?php if ( ! empty( $hero['labelStrong'] ) ) : ?> — <b><?php echo lindo_lines( $hero['labelStrong'] ); ?></b><?php endif; ?></p>
			<p class="meta"><?php echo lindo_lines( isset( $hero['meta'] ) ? $hero['meta'] : '' ); ?></p>
		</div>
		<h1 class="hero-title" data-hero-fx="scatter">
			<span class="ln"><span class="ln-text" data-line><?php echo esc_html( isset( $hero['line1'] ) ? $hero['line1'] : '' ); ?></span></span>
			<span class="ln"><span class="ln-text" data-line><?php echo esc_html( isset( $hero['line2'] ) ? $hero['line2'] : '' ); ?><span class="pk">.</span></span></span>
		</h1>
		<div class="hero-row">
			<p><?php echo lindo_lines( isset( $hero['lead'] ) ? $hero['lead'] : '' ); ?></p>
			<p class="tags"><?php echo lindo_lines( isset( $hero['tags'] ) ? $hero['tags'] : '' ); ?></p>
		</div>
	</div>
</section>
