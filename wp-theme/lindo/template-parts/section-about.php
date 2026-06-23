<?php
/**
 * About（01）。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$representative = isset( $representative ) && is_array( $representative ) ? $representative : array();
$rep_name       = isset( $representative['name'] ) ? $representative['name'] : '';
?>
<section class="sec" id="about">
	<div class="wrap sec-grid">
		<div class="sec-no rv">01<small>About</small></div>
		<div class="sec-body rv d1">
			<h2>ビジュアルのコンセプトを最重要視する。</h2>
			<p class="sub">K-POPのブランディング実績を生かし、アーティストのイメージ作り・ストーリー設計、アルバムや広告のビジュアルコンセプト企画／制作を行います。写真・映像・衣装・スタイリングを含むビジュアルクリエイティブを、トータルで。</p>

			<?php if ( '' !== $rep_name ) : ?>
				<div class="about-rep rv d2">
					<span class="rep-label">代表 <span aria-hidden="true">/</span> Representative</span>
					<div class="rep-head">
						<span class="rep-name"><?php echo esc_html( $rep_name ); ?></span>
						<?php if ( ! empty( $representative['title'] ) ) : ?>
							<span class="rep-title"><?php echo esc_html( $representative['title'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $representative['profile'] ) ) : ?>
						<div class="rep-bio">
							<?php foreach ( $representative['profile'] as $para ) : ?>
								<p><?php echo esc_html( $para ); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
