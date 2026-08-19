<?php
/**
 * Service（02）。
 *
 * 期待する変数:
 *   $service  array{label, items: array<int,array{title,description}>}
 *
 * 連番（01,02…）は表示順から自動で振る。CMS に入力させると、行を並べ替えたときに
 * 番号だけ取り残されて必ずズレる。
 *
 * このセクションだけスクロールの出現アニメーション（.rv）を付けていない。
 * 事業内容は一覧として一度に読ませたい箇所で、順に浮き上がると視線が散るため。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$service = isset( $service ) && is_array( $service ) ? $service : array();
$items   = isset( $service['items'] ) && is_array( $service['items'] ) ? $service['items'] : array();
?>
<section class="sec" id="service">
	<div class="wrap sec-grid">
		<div class="sec-no">02<small><?php echo esc_html( isset( $service['label'] ) ? $service['label'] : '' ); ?></small></div>
		<div class="sec-body">
			<h2><?php echo esc_html( isset( $service['label'] ) ? $service['label'] : '' ); ?></h2>
			<div class="svc">
				<?php foreach ( $items as $i => $svc ) : ?>
					<div class="svc-item">
						<span class="i"><?php echo esc_html( sprintf( '%02d', (int) $i + 1 ) ); ?></span>
						<span class="t"><?php echo esc_html( isset( $svc['title'] ) ? $svc['title'] : '' ); ?></span>
						<span class="d"><?php echo lindo_lines( isset( $svc['description'] ) ? $svc['description'] : '' ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
