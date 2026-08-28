<?php
/**
 * Artists（04）— 関わっているアーティストの一覧。
 *
 * 03 Works との違い:
 *   03 Works   … LINDO が手がけた**作品**。カード → モーダルでギャラリー。
 *   04 Artists … アーティスト**そのもの**。カード → 公式サイト（外部）へ遷移。
 *
 * 期待する変数:
 *   $talents  array{label,lead}（セクションの文言。site-defaults.php 由来）
 *   $items    array<int,array{name,name_sub,url,image:{url,w,h,alt}}>
 *
 * 1件も無いときはセクションごと非表示にする。「Artists」という見出しだけが
 * 空で残るのは、載せ忘れなのか準備中なのか外から判断できないため。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$talents = isset( $talents ) && is_array( $talents ) ? $talents : array();
$items   = isset( $items ) && is_array( $items ) ? $items : array();
if ( empty( $items ) ) {
	return;
}
$label = isset( $talents['label'] ) ? (string) $talents['label'] : 'Artists';
$lead  = isset( $talents['lead'] ) ? (string) $talents['lead'] : '';
?>
<section class="sec" id="talents">
	<div class="wrap sec-grid">
		<div class="sec-no rv">04<small><?php echo esc_html( $label ); ?></small></div>
		<div class="sec-body rv d1">
			<?php // 左の「04 Artists」と同じ語なので視覚的には出さない。読み上げ・文書構造のためだけに置く。 ?>
			<h2 class="sr-only"><?php echo esc_html( $label ); ?></h2>
			<?php if ( '' !== $lead ) : ?>
				<p class="sub"><?php echo lindo_lines( $lead ); ?></p>
			<?php endif; ?>

			<div class="talents">
				<?php foreach ( $items as $i => $t ) : ?>
					<?php
					$name = isset( $t['name'] ) ? (string) $t['name'] : '';
					if ( '' === $name ) {
						continue;
					}
					$img = isset( $t['image'] ) && is_array( $t['image'] ) ? $t['image'] : array();
					$url = isset( $t['url'] ) ? trim( (string) $t['url'] ) : '';
					// 公式サイトが未登録なら、リンクにせず紹介だけ出す。
					// href="" のままにすると、押したときページが再読込されて壊れて見える。
					$tag = '' !== $url ? 'a' : 'div';
					?>
					<<?php echo $tag; ?>
						class="talent-card rv"
						style="--ci:<?php echo (int) $i; ?>"
						<?php if ( '' !== $url ) : ?>
							href="<?php echo esc_url( $url ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							aria-label="<?php echo esc_attr( sprintf( '%s の公式サイトを新しいタブで開く', $name ) ); ?>"
						<?php endif; ?>
					>
						<span class="ph">
							<?php if ( ! empty( $img['url'] ) ) : ?>
								<img
									src="<?php echo esc_url( $img['url'] ); ?>"
									width="<?php echo esc_attr( isset( $img['w'] ) ? $img['w'] : '' ); ?>"
									height="<?php echo esc_attr( isset( $img['h'] ) ? $img['h'] : '' ); ?>"
									alt="<?php echo esc_attr( ! empty( $img['alt'] ) ? $img['alt'] : $name ); ?>"
									loading="lazy"
									decoding="async"
								/>
							<?php endif; ?>
						</span>
						<span class="talent-meta">
							<span class="nm"><?php echo esc_html( $name ); ?></span>
							<?php if ( '' !== $url ) : ?>
								<span class="go">Official Site <span aria-hidden="true">↗</span></span>
							<?php endif; ?>
						</span>
						<?php if ( ! empty( $t['name_sub'] ) ) : ?>
							<span class="talent-sub"><?php echo esc_html( $t['name_sub'] ); ?></span>
						<?php endif; ?>
					</<?php echo $tag; ?>>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
