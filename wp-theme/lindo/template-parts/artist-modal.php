<?php
/**
 * アーティスト・モーダル（native <dialog>）。
 * 情報＋Instagram風グリッド。内容はDOMに実在＝SEO/no-JSでもクロール可。
 *
 * 期待する変数: $artist（lindo_get_artist_data の戻り）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
if ( empty( $artist ) || empty( $artist['name'] ) ) {
	return;
}
$portrait = $artist['portrait'];
$gallery  = $artist['gallery'];
$title_id = $artist['slug'] . '-title';
?>
<dialog class="artist-modal" id="<?php echo esc_attr( $artist['slug'] ); ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="artist-modal-scroll" data-modal-scroll>
		<div class="modal-close">
			<button type="button" data-modal-close aria-label="閉じる">✕</button>
		</div>

		<div class="modal-head">
			<div class="modal-portrait">
				<?php if ( $portrait ) : ?>
					<img
						src="<?php echo esc_url( $portrait['url'] ); ?>"
						width="<?php echo esc_attr( $portrait['w'] ); ?>"
						height="<?php echo esc_attr( $portrait['h'] ); ?>"
						alt="<?php echo esc_attr( $portrait['alt'] ? $portrait['alt'] : $artist['name'] ); ?>"
						loading="lazy"
						decoding="async"
					/>
				<?php endif; ?>
			</div>
			<div class="modal-info">
				<?php if ( $artist['role'] ) : ?>
					<p class="eyebrow"><?php echo esc_html( $artist['role'] ); ?></p>
				<?php endif; ?>
				<h3 id="<?php echo esc_attr( $title_id ); ?>">
					<?php echo esc_html( $artist['name'] ); ?>
					<?php if ( $artist['name_sub'] ) : ?>
						<small><?php echo esc_html( $artist['name_sub'] ); ?></small>
					<?php endif; ?>
				</h3>

				<?php if ( ! empty( $artist['profile'] ) ) : ?>
					<div class="bio">
						<?php foreach ( $artist['profile'] as $para ) : ?>
							<p><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $artist['tags'] ) ) : ?>
					<div class="modal-tags">
						<?php foreach ( $artist['tags'] as $tag ) : ?>
							<span><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $artist['links'] ) ) : ?>
					<div class="modal-links">
						<?php foreach ( $artist['links'] as $link ) : ?>
							<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $link['label'] ); ?> <span aria-hidden="true">↗</span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( ! empty( $gallery ) ) : ?>
			<div class="ig-grid-head">
				<span class="t">Gallery</span>
				<span class="c"><?php echo esc_html( sprintf( '%d posts', count( $gallery ) ) ); ?></span>
			</div>
			<div class="ig-grid">
				<?php foreach ( $gallery as $gi => $img ) : ?>
					<div class="ig-cell" style="--ci:<?php echo (int) $gi; ?>">
						<img
							src="<?php echo esc_url( $img['url'] ); ?>"
							width="<?php echo esc_attr( $img['w'] ); ?>"
							height="<?php echo esc_attr( $img['h'] ); ?>"
							alt="<?php echo esc_attr( $img['alt'] ? $img['alt'] : $artist['name'] ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</dialog>
