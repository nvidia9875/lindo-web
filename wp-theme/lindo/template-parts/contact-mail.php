<?php
/**
 * Contact — メール導線（フォームの代わりに出す暫定の中身）。
 *
 * 【なぜフォームではないのか】
 * 送信の実装がまだ無い（方式が公開日の判断待ち: 同一オリジンの Cloudflare Workers か、
 * 外部サービスか）。実装が無いまま見た目だけのフォームを置くと、押しても何も起きず
 * 問い合わせを丸ごと取りこぼす。届かないフォームより、確実に届くメール導線を出す。
 *
 * 【フォームに戻すとき】
 * `contact-form-fallback.php` は消していない。preview/render.php の
 * LINDO_CONTACT_PART を 'contact-form-fallback' に戻すだけで戻る。
 * 手順は同ファイルの冒頭コメント参照。
 *
 * フォームが持っていた「何を書けばいいか」の案内は項目リストとして残してある。
 * これが無いと本文が一行だけのメールが増え、往復が発生する。
 *
 * メールアドレスそのものは左カラム（section-contact.php の .direct）が出すので、
 * ここでは重ねない。
 *
 * 期待する変数:
 *   $contact array{label,lead,email}
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$contact_email = isset( $contact['email'] ) ? trim( (string) $contact['email'] ) : '';
if ( '' === $contact_email ) {
	return; // 宛先が無いなら何も出さない（押せないCTAを出すよりよい）。
}

// 件名だけ埋める。本文まで差し込むとメーラーによって崩れる。
$mailto = 'mailto:' . rawurlencode( $contact_email ) . '?subject=' . rawurlencode( 'お仕事のご依頼・ご相談' );

$hints = array(
	'お名前 / 会社名',
	'ご依頼の内容（ビジュアルクリエイティブ / ブランディング / スタイリング など）',
	'ご予算・ご希望の時期',
);
?>
<div class="contact-mail">
	<p class="contact-mail__label">Email</p>
	<p class="contact-mail__note">下記をお書き添えいただけると、ご返信がスムーズです。</p>
	<ul class="contact-mail__hints">
		<?php foreach ( $hints as $hint ) : ?>
			<li><?php echo esc_html( $hint ); ?></li>
		<?php endforeach; ?>
	</ul>
	<p class="contact-mail__action">
		<a class="btn" href="<?php echo esc_url( $mailto ); ?>">メールを作成する</a>
	</p>
</div>
