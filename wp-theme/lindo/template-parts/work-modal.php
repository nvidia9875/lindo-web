<?php
/**
 * 作品モーダル（native <dialog>）。
 * Works 章レイアウトで、作品のカバー画像を押すと開く。
 * その作品の複数画像を Instagram 風グリッドで表示（既存モーダルの体裁を流用）。
 *
 * 期待する変数:
 *   $work         array{slug,title,cover,gallery[]}
 *   $artist_name  string（見出し上のアーティスト名）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
if ( empty( $work ) || empty( $work['slug'] ) ) {
	return;
}
$gallery     = isset( $work['gallery'] ) ? $work['gallery'] : array();
$artist_name = isset( $artist_name ) ? $artist_name : '';
$title_id    = $work['slug'] . '-title';
?>
<dialog class="artist-modal work-modal" id="<?php echo esc_attr( $work['slug'] ); ?>" aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
	<div class="artist-modal-scroll" data-modal-scroll>
		<div class="modal-close">
			<button type="button" data-modal-close aria-label="閉じる">✕</button>
		</div>

		<div class="modal-info work-modal-info">
			<?php if ( $artist_name ) : ?>
				<p class="eyebrow"><?php echo esc_html( $artist_name ); ?></p>
			<?php endif; ?>
			<h3 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $work['title'] ); ?></h3>
			<?php if ( ! empty( $work['role'] ) ) : ?>
				<p class="work-role"><?php echo esc_html( $work['role'] ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $gallery ) ) : ?>
			<div class="ig-grid-head">
				<span class="t">Gallery</span>
				<span class="c"><?php echo esc_html( sprintf( '%d images', count( $gallery ) ) ); ?></span>
			</div>
			<div class="ig-grid">
				<?php foreach ( $gallery as $gi => $img ) : ?>
					<div class="ig-cell" style="--ci:<?php echo (int) $gi; ?>">
						<img
							src="<?php echo esc_url( $img['url'] ); ?>"
							width="<?php echo esc_attr( $img['w'] ); ?>"
							height="<?php echo esc_attr( $img['h'] ); ?>"
							alt="<?php echo esc_attr( $img['alt'] ? $img['alt'] : $work['title'] ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</dialog>
