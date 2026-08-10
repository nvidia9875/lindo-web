<?php
/**
 * お問い合わせフォーム（Web3Forms）。
 *
 * **アクセスキーが設定されているときだけ出る。** 未設定なら render.php が
 * `contact-mail`（メール導線）を出す。見た目だけのフォームを絶対に出さないための作り
 * ＝「押しても何も起きない」状態が構造的に発生しない。
 *
 * ── 送信の流れ ──────────────────────────────────
 *   ブラウザ → POST https://api.web3forms.com/submit → 指定アドレスへメール
 *              → $redirect（thanks.html）へ遷移
 *
 * ── 設定 ────────────────────────────────────────
 *   環境変数 WEB3FORMS_ACCESS_KEY（CIでは Actions の Variable）
 *   ※ アクセスキーは HTML に出るので秘密ではない（仕様上そういうもの）。
 *     Secret ではなく Variable でよい。悪用されたら管理画面で再発行する。
 *
 * ── hCaptcha を入れていない理由 ──────────────────
 *   web3forms.com の外部スクリプトが必要で、CSP の `script-src 'self'` に反する。
 *   JSを自前ホストする方針とも衝突するため、ハニーポット（botcheck）のみにしてある。
 *   無料枠が250通/月あるので、スパムで本物の問い合わせが落ちる余地は小さい。
 *
 * 期待する変数:
 *   $contact     array{label,lead,email}
 *   $form_key    string  Web3Forms のアクセスキー
 *   $form_redirect string 送信後の遷移先（絶対URL）
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}

$form_key      = isset( $form_key ) ? trim( (string) $form_key ) : '';
$form_redirect = isset( $form_redirect ) ? trim( (string) $form_redirect ) : '';
if ( '' === $form_key ) {
	return; // 呼び出し側で弾いているはずだが、二重の保険。
}
?>
<form class="lindo-form" action="https://api.web3forms.com/submit" method="POST">
	<input type="hidden" name="access_key" value="<?php echo esc_attr( $form_key ); ?>" />
	<input type="hidden" name="subject" value="styledbylindo.com からのお問い合わせ" />
	<input type="hidden" name="from_name" value="LINDO Website" />
	<?php if ( '' !== $form_redirect ) : ?>
		<input type="hidden" name="redirect" value="<?php echo esc_url( $form_redirect ); ?>" />
	<?php endif; ?>

	<p class="field">
		<label for="cf-name">お名前 / 会社名<span class="req">*</span></label>
		<input type="text" id="cf-name" name="name" autocomplete="organization" placeholder="株式会社○○ ／ 山田 太郎" required />
	</p>
	<p class="field">
		<label for="cf-email">メールアドレス<span class="req">*</span></label>
		<?php // name="email" にしておくと Web3Forms が自動で Reply-To に使う（そのまま返信できる）。 ?>
		<input type="email" id="cf-email" name="email" autocomplete="email" placeholder="you@example.com" required />
	</p>
	<p class="field">
		<label for="cf-type">ご相談の種類</label>
		<?php // name="subject" は上の固定件名と衝突するので使わない。 ?>
		<select id="cf-type" name="ご相談の種類">
			<option value="ビジュアルクリエイティブ">ビジュアルクリエイティブ</option>
			<option value="ブランディング">ブランディング</option>
			<option value="スタイリング">スタイリング</option>
			<option value="その他">その他</option>
		</select>
	</p>
	<p class="field">
		<label for="cf-message">メッセージ<span class="req">*</span></label>
		<textarea id="cf-message" name="message" placeholder="ご依頼内容・ご予算・希望時期など" required></textarea>
	</p>

	<?php // ハニーポット。人間には見えないので、埋まっていればボット。 ?>
	<input type="checkbox" name="botcheck" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true" />

	<p class="field">
		<button type="submit" class="btn">Send Message</button>
	</p>
</form>
