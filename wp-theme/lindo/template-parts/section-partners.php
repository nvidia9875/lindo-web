<?php
/**
 * Partner（04）— 主要取引先。
 *
 * 期待する変数: $partners（社名の配列。本番は inc/partners.php の Customizer 由来）
 * 空のときはセクションごと非表示。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$partners = isset( $partners ) && is_array( $partners ) ? $partners : array();
if ( empty( $partners ) ) {
	return;
}
?>
<section class="sec">
	<div class="wrap sec-grid">
		<div class="sec-no rv">04<small>Business Partner</small></div>
		<div class="sec-body rv d1">
			<h2>Business Partner</h2>
			<div class="cl">
				<?php foreach ( $partners as $partner ) : ?>
					<span><?php echo esc_html( $partner ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
