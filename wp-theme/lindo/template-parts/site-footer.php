<?php
/**
 * サイトフッター（会社情報）。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_year = isset( $lindo_year ) ? $lindo_year : '2026';
?>
<footer class="ft">
	<div class="wrap ft-in">
		<div class="b">LIND<span>O</span></div>
		<div class="a">株式会社LINDO（LINDO Co.,Ltd.）<br>〒151-0066 東京都渋谷区西原2-34-9</div>
		<div class="c">
			<a href="tel:0353085822">tel. 03-5308-5822</a>
			<a href="mailto:contact@styledbylindo.com">contact@styledbylindo.com</a>
		</div>
		<div class="cp">© <?php echo esc_html( $lindo_year ); ?> 株式会社LINDO ・ Visual Creative Studio</div>
	</div>
</footer>
