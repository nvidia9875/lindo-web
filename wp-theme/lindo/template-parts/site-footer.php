<?php
/**
 * サイトフッター（会社情報）。
 *
 * 期待する変数:
 *   $lindo_year     string
 *   $contact_email  string（Customizer「LINDO — Contact」由来。直書きしないこと＝
 *                   設定を変えたときフッターだけ古いアドレスが残るため）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$lindo_year    = isset( $lindo_year ) ? $lindo_year : '2026';
$contact_email = isset( $contact_email ) && '' !== $contact_email ? $contact_email : 'contact@styledbylindo.com';
?>
<footer class="ft">
	<div class="wrap ft-in">
		<div class="b">LIND<span>O</span></div>
		<div class="a">株式会社LINDO（LINDO Co.,Ltd.）<br>〒151-0066 東京都渋谷区西原2-34-9</div>
		<div class="c">
			<a href="tel:0353085822">tel. 03-5308-5822</a>
			<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
		</div>
		<div class="cp">© <?php echo esc_html( $lindo_year ); ?> 株式会社LINDO ・ Visual Creative Studio</div>
	</div>
</footer>
