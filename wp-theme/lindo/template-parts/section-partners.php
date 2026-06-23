<?php
/**
 * Partner（04）— 主要取引先。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_partners = array(
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
?>
<section class="sec">
	<div class="wrap sec-grid">
		<div class="sec-no rv">04<small>Business Partner</small></div>
		<div class="sec-body rv d1">
			<h2>Business Partner</h2>
			<div class="cl">
				<?php foreach ( $lindo_partners as $partner ) : ?>
					<span><?php echo esc_html( $partner ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
