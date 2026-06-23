<?php
/**
 * Service（02）。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_services = array(
	array( '01', 'Visual Creative', 'アーティスト・アルバム・出演番組に合わせたビジュアルの企画／制作。' ),
	array( '02', 'Branding', '戦略的なイメージ設計。ストーリーから世界観を強固に。' ),
	array( '03', 'Styling', '衣装・ヘアメイクを含むトータルスタイリング、アサイン／協業。' ),
	array( '04', 'Design Direction', 'B.I（ロゴ）など、デザインの方向性を設計。' ),
);
?>
<section class="sec" id="service">
	<div class="wrap sec-grid">
		<div class="sec-no rv">02<small>What We Do</small></div>
		<div class="sec-body rv d1">
			<h2>What We Do</h2>
			<div class="svc">
				<?php foreach ( $lindo_services as $svc ) : ?>
					<div class="svc-item">
						<span class="i"><?php echo esc_html( $svc[0] ); ?></span>
						<span class="t"><?php echo esc_html( $svc[1] ); ?></span>
						<span class="d"><?php echo esc_html( $svc[2] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
