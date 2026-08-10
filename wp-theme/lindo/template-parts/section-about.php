<?php
/**
 * About（01）。
 *
 * 期待する変数:
 *   $about  array{label,heading,body}
 *   $rep    array{name,title,profile}  profile は空行区切りの生テキスト
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$about    = isset( $about ) && is_array( $about ) ? $about : array();
$rep      = isset( $rep ) && is_array( $rep ) ? $rep : array();
$rep_name = isset( $rep['name'] ) ? trim( (string) $rep['name'] ) : '';
?>
<section class="sec" id="about">
	<div class="wrap sec-grid">
		<div class="sec-no rv">01<small><?php echo esc_html( isset( $about['label'] ) ? $about['label'] : '' ); ?></small></div>
		<div class="sec-body rv d1">
			<?php // 見出しの改行は <wbr>（折り返してよい位置）。本文の <br> とは規則が違う。 ?>
			<h2><?php echo lindo_heading( isset( $about['heading'] ) ? $about['heading'] : '' ); ?></h2>
			<p class="sub"><?php echo lindo_lines( isset( $about['body'] ) ? $about['body'] : '' ); ?></p>

			<?php if ( '' !== $rep_name ) : ?>
				<div class="about-rep rv d2">
					<span class="rep-label">代表 <span aria-hidden="true">/</span> Representative</span>
					<div class="rep-head">
						<span class="rep-name"><?php echo esc_html( $rep_name ); ?></span>
						<?php if ( ! empty( $rep['title'] ) ) : ?>
							<span class="rep-title"><?php echo esc_html( $rep['title'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php $rep_paras = lindo_split_paras( isset( $rep['profile'] ) ? $rep['profile'] : '' ); ?>
					<?php if ( ! empty( $rep_paras ) ) : ?>
						<div class="rep-bio">
							<?php foreach ( $rep_paras as $para ) : ?>
								<p><?php echo lindo_lines( $para ); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
