<?php
/**
 * アーティスト一覧カード（モーダルを開くトリガ）。
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
$ci       = max( 0, (int) $artist['index'] - 1 );
?>
<button
	type="button"
	class="artist-card rv"
	style="--ci:<?php echo (int) $ci; ?>"
	data-modal-target="<?php echo esc_attr( $artist['slug'] ); ?>"
	aria-haspopup="dialog"
	aria-label="<?php echo esc_attr( sprintf( '%s の詳細を見る', $artist['name'] ) ); ?>"
>
	<span class="ph">
		<span class="idx"><?php echo esc_html( $artist['index'] ); ?></span>
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
		<span class="open-flag">View <span aria-hidden="true">↗</span></span>
	</span>
	<span class="artist-meta">
		<span class="nm">
			<?php echo esc_html( $artist['name'] ); ?>
			<?php if ( $artist['name_sub'] ) : ?>
				<small><?php echo esc_html( $artist['name_sub'] ); ?></small>
			<?php endif; ?>
		</span>
		<?php if ( $artist['role'] ) : ?>
			<span class="role"><?php echo esc_html( $artist['role'] ); ?></span>
		<?php endif; ?>
	</span>
</button>
