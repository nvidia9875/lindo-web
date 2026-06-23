<?php
/**
 * Contact — 問い合わせ。
 * フォーム本体は $contact_form_html を流し込む
 *   - 本番: Contact Form 7 の do_shortcode 出力
 *   - 未設定/プレビュー: 静的フォールバックフォーム（contact.php が生成）
 *
 * 期待する変数:
 *   $contact_form_html  フォームHTML（信頼済みソース）
 *   $contact_email      直接連絡用メール（既定 contact@styledbylindo.com）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$contact_form_html = isset( $contact_form_html ) ? $contact_form_html : '';
$contact_email     = isset( $contact_email ) ? $contact_email : 'contact@styledbylindo.com';
?>
<section class="contact" id="contact">
	<div class="wrap contact-grid">
		<div class="contact-lead rv">
			<h2>Contact<span class="pk">.</span></h2>
			<p class="sub">お仕事のご依頼・ご相談はこちらから。内容を確認のうえ、担当者よりご連絡いたします。</p>
			<p class="direct">
				Direct —
				<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
			</p>
		</div>
		<div class="contact-form-wrap rv d1">
			<?php
			// 信頼済みのフォームHTML（CF7出力 or テーマ生成フォールバック）。
			echo $contact_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</div>
</section>
