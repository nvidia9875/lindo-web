<?php
/**
 * Works（章レイアウト）— アーティストごとに大きな区切りを設け、
 * その中にそのアーティストの「作品（Works）」を inline で並べる。
 * 多数のアーティスト（〜10件以上）前提。
 *
 * 構造: アーティスト（章） → 作品（フィルムストリップの1枚＝1作品） → 作品の複数画像（モーダル）。
 *   - 各作品 = カバー画像1枚 ＋ 画像下にタイトルを重ねる ＋ クリックでモーダル（複数画像）。
 *   - カバー画像はモーダルを開くトリガ（外部リンクではない）。MV等の動画は wf-links に出す想定。
 *
 * 期待する変数: $artists（各 artist に works[] を含む）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$artists = isset( $artists ) && is_array( $artists ) ? $artists : array();
?>
<section class="sec works-feature" id="works">
	<div class="wrap wf-intro rv">
		<div class="sec-no-inline">03<small>Works</small></div>
		<h2>Works</h2>
		<p class="sub">アーティストごとに、これまで手がけた作品をご紹介します。作品を選ぶと、その制作の画像をまとめてご覧いただけます。</p>
	</div>

	<?php
	foreach ( $artists as $artist ) :
		if ( empty( $artist['name'] ) ) {
			continue;
		}
		$works = isset( $artist['works'] ) && is_array( $artist['works'] ) ? $artist['works'] : array();
		?>
		<article class="wf-artist rv" id="wf-<?php echo esc_attr( $artist['slug'] ); ?>">
			<div class="wrap wf-head">
				<span class="wf-no"><?php echo esc_html( $artist['index'] ); ?></span>
				<div class="wf-id">
					<h3 class="wf-name">
						<?php echo esc_html( $artist['name'] ); ?>
						<?php if ( $artist['name_sub'] ) : ?>
							<small><?php echo esc_html( $artist['name_sub'] ); ?></small>
						<?php endif; ?>
					</h3>
					<?php if ( $artist['role'] ) : ?>
						<span class="wf-role"><?php echo esc_html( $artist['role'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $artist['links'] ) ) : ?>
					<div class="wf-links">
						<?php foreach ( $artist['links'] as $link ) : ?>
							<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $link['label'] ); ?> <span aria-hidden="true">↗</span></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $works ) ) : ?>
				<div class="wf-strip wrap" role="list" aria-label="<?php echo esc_attr( sprintf( '%s の作品', $artist['name'] ) ); ?>">
					<?php
					foreach ( $works as $work ) :
						$cover = isset( $work['cover'] ) ? $work['cover'] : null;
						?>
						<button
							type="button"
							class="wf-item"
							role="listitem"
							data-modal-target="<?php echo esc_attr( $work['slug'] ); ?>"
							aria-haspopup="dialog"
							aria-label="<?php echo esc_attr( sprintf( '%s — 作品の画像を見る', $work['title'] ) ); ?>"
						>
							<?php if ( $cover ) : ?>
								<img
									src="<?php echo esc_url( $cover['url'] ); ?>"
									width="<?php echo esc_attr( $cover['w'] ); ?>"
									height="<?php echo esc_attr( $cover['h'] ); ?>"
									alt="<?php echo esc_attr( $cover['alt'] ? $cover['alt'] : $work['title'] ); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php endif; ?>
							<span class="wf-view">View <span aria-hidden="true">↗</span></span>
							<span class="wf-cap"><?php echo esc_html( $work['title'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</section>

<?php
// 作品モーダル（DOM末尾にまとめて出力）。各作品の複数画像を表示。
foreach ( $artists as $artist ) {
	if ( empty( $artist['name'] ) || empty( $artist['works'] ) ) {
		continue;
	}
	foreach ( $artist['works'] as $work ) {
		lindo_part(
			'work-modal',
			array(
				'work'        => $work,
				'artist_name' => $artist['name'],
			)
		);
	}
}
