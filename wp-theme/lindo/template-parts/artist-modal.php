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
$works    = isset( $artist['works'] ) && is_array( $artist['works'] ) ? $artist['works'] : array();
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

		<?php if ( ! empty( $works ) ) : ?>
			<?php
			// プレビュー（manifest 由来）：作品ごとにグループ表示。
			// url を持つ work は MV 等の外部リンクタイル（ライトボックスではなく外部遷移）。
			foreach ( $works as $work ) :
				$w_gallery = isset( $work['gallery'] ) && is_array( $work['gallery'] ) ? $work['gallery'] : array();
				$w_url     = isset( $work['url'] ) ? $work['url'] : '';
				if ( empty( $w_gallery ) ) {
					continue;
				}
				$w_title = isset( $work['title'] ) ? $work['title'] : $artist['name'];
				?>
				<div class="ig-grid-head">
					<span class="t"><?php echo esc_html( $w_title ); ?></span>
					<?php if ( $w_url ) : ?>
						<span class="c">MV</span>
					<?php else : ?>
						<span class="c"><?php echo esc_html( sprintf( '%d posts', count( $w_gallery ) ) ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $w_url ) : ?>
					<?php $cover = $w_gallery[0]; ?>
					<div class="ig-grid ig-grid--link">
						<a
							class="ig-cell ig-cell--link"
							href="<?php echo esc_url( $w_url ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( sprintf( '%s を見る（YouTube・別タブで開く）', $w_title ) ); ?>"
						>
							<img
								src="<?php echo esc_url( $cover['url'] ); ?>"
								width="<?php echo esc_attr( $cover['w'] ); ?>"
								height="<?php echo esc_attr( $cover['h'] ); ?>"
								alt="<?php echo esc_attr( $cover['alt'] ? $cover['alt'] : $w_title ); ?>"
								loading="lazy"
								decoding="async"
							/>
							<span class="ig-play" aria-hidden="true">▶</span>
							<span class="ig-link-cap"><?php echo esc_html( $w_title ); ?> <span aria-hidden="true">↗</span></span>
						</a>
					</div>
				<?php else : ?>
					<div class="ig-grid">
						<?php foreach ( $w_gallery as $gi => $img ) : ?>
							<button
								type="button"
								class="ig-cell"
								style="--ci:<?php echo (int) $gi; ?>"
								aria-label="<?php echo esc_attr( sprintf( '%s の写真 %d を拡大表示', $w_title, $gi + 1 ) ); ?>"
							>
								<img
									src="<?php echo esc_url( $img['url'] ); ?>"
									width="<?php echo esc_attr( $img['w'] ); ?>"
									height="<?php echo esc_attr( $img['h'] ); ?>"
									alt="<?php echo esc_attr( $img['alt'] ? $img['alt'] : $w_title ); ?>"
									loading="lazy"
									decoding="async"
								/>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php elseif ( ! empty( $gallery ) ) : ?>
			<?php // 本番WPフォールバック：フラットな1ギャラリー（Instagram風）。 ?>
			<div class="ig-grid-head">
				<span class="t">Gallery</span>
				<span class="c"><?php echo esc_html( sprintf( '%d posts', count( $gallery ) ) ); ?></span>
			</div>
			<div class="ig-grid">
				<?php foreach ( $gallery as $gi => $img ) : ?>
					<button
						type="button"
						class="ig-cell"
						style="--ci:<?php echo (int) $gi; ?>"
						aria-label="<?php echo esc_attr( sprintf( '%s の写真 %d を拡大表示', $artist['name'], $gi + 1 ) ); ?>"
					>
						<img
							src="<?php echo esc_url( $img['url'] ); ?>"
							width="<?php echo esc_attr( $img['w'] ); ?>"
							height="<?php echo esc_attr( $img['h'] ); ?>"
							alt="<?php echo esc_attr( $img['alt'] ? $img['alt'] : $artist['name'] ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</dialog>
