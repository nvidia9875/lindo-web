<?php
/**
 * フォールバックの静的フォーム（デザイン確認用 / CF7 未導入時）。
 * 実送信は行わない。本番では CF7 ショートコードを設定して置き換える。
 *
 * @package LINDO
 */

if ( ! defined( 'LINDO_PART' ) ) {
	exit;
}
?>
<form class="lindo-form" data-fallback-form action="#" method="post" novalidate>
	<p class="field">
		<label for="cf-name">お名前 / 会社名<span class="req">*</span></label>
		<input type="text" id="cf-name" name="cf-name" autocomplete="organization" placeholder="株式会社○○ ／ 山田 太郎" required />
	</p>
	<p class="field">
		<label for="cf-email">メールアドレス<span class="req">*</span></label>
		<input type="email" id="cf-email" name="cf-email" autocomplete="email" placeholder="you@example.com" required />
	</p>
	<p class="field">
		<label for="cf-subject">ご相談の種類</label>
		<select id="cf-subject" name="cf-subject">
			<option value="ビジュアルクリエイティブ">ビジュアルクリエイティブ</option>
			<option value="ブランディング">ブランディング</option>
			<option value="スタイリング">スタイリング</option>
			<option value="その他">その他</option>
		</select>
	</p>
	<p class="field">
		<label for="cf-message">メッセージ<span class="req">*</span></label>
		<textarea id="cf-message" name="cf-message" placeholder="ご依頼内容・ご予算・希望時期など" required></textarea>
	</p>
	<p class="field">
		<button type="submit" class="btn">Send Message</button>
	</p>
</form>
