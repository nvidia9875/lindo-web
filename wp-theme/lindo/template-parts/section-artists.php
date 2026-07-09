<?php
/**
 * Artists（03）— Works を置換する主役セクション。
 * カード一覧 → クリックでモーダル（情報＋作品ごとのグループ・ギャラリー）。
 *
 * 期待する変数:
 *   $artists  array<int,array> = lindo_get_artist_data の配列
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$artists = isset( $artists ) && is_array( $artists ) ? $artists : array();
$count   = count( $artists );
?>
<section class="sec" id="artists">
	<div class="wrap sec-grid">
		<div class="sec-no rv">03<small>Works</small></div>
		<div class="sec-body rv d1">
			<h2>Works</h2>
			<p class="sub">私たちが手がけたアーティストのビジュアルワーク。名前を選ぶと、プロフィールと作品ギャラリーをご覧いただけます。</p>

			<?php if ( $count ) : ?>
				<div class="artists">
					<?php foreach ( $artists as $artist ) : ?>
						<?php lindo_part( 'artist-card', array( 'artist' => $artist ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="sub" style="margin-top:1.6rem">準備中です。まもなく公開します。</p>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
// モーダルは section 外（DOM末尾）にまとめて出力。
foreach ( $artists as $artist ) {
	lindo_part( 'artist-modal', array( 'artist' => $artist ) );
}
