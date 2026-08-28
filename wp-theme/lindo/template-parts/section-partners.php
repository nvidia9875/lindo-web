<?php
/**
 * Partner（05）— 主要取引先。
 *
 * 期待する変数:
 *   $partners  array{label, items: array<int,string>}
 *
 * 取引先が空のときはセクションごと非表示。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$partners = isset( $partners ) && is_array( $partners ) ? $partners : array();
$items    = isset( $partners['items'] ) && is_array( $partners['items'] ) ? $partners['items'] : array();
if ( empty( $items ) ) {
	return;
}
?>
<section class="sec">
	<div class="wrap sec-grid">
		<div class="sec-no rv">05<small><?php echo esc_html( isset( $partners['label'] ) ? $partners['label'] : '' ); ?></small></div>
		<div class="sec-body rv d1">
			<h2><?php echo esc_html( isset( $partners['label'] ) ? $partners['label'] : '' ); ?></h2>
			<div class="cl">
				<?php foreach ( $items as $partner ) : ?>
					<span><?php echo esc_html( $partner ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
