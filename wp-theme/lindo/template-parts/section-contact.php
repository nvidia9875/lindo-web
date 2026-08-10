<?php
/**
 * Contact — 問い合わせ。
 * 右カラムの中身は $contact_body_html を流し込む
 *   - 現行（静的サイト）: メール導線（contact-mail）。フォームの送信実装が未着手のため
 *   - WordPress: Contact Form 7 の do_shortcode 出力
 *
 * 期待する変数:
 *   $contact           array{label,lead,email}
 *   $contact_body_html 右カラムのHTML（信頼済みソース）
 *
 * 見出し末尾のピンクの「.」は装飾なのでCMSには入力させず、ここで付ける。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
$contact           = isset( $contact ) && is_array( $contact ) ? $contact : array();
$contact_body_html = isset( $contact_body_html ) ? $contact_body_html : '';
$contact_email     = isset( $contact['email'] ) ? (string) $contact['email'] : '';
?>
<section class="contact" id="contact">
	<div class="wrap contact-grid">
		<div class="contact-lead rv">
			<h2><?php echo esc_html( isset( $contact['label'] ) ? $contact['label'] : '' ); ?><span class="pk">.</span></h2>
			<p class="sub"><?php echo lindo_lines( isset( $contact['lead'] ) ? $contact['lead'] : '' ); ?></p>
			<?php if ( '' !== $contact_email ) : ?>
				<p class="direct">
					Direct —
					<a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
				</p>
			<?php endif; ?>
		</div>
		<div class="contact-form-wrap rv d1">
			<?php
			// 信頼済みのHTML（メール導線 or CF7出力）。
			echo $contact_body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</div>
	</div>
</section>
