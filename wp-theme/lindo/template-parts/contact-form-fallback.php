<?php
/**
 * 静的フォーム。**現在は使っていない（差し戻し用に保存してある）。**
 *
 * 送信の実装が無く、押しても何も起きずに問い合わせを取りこぼすため、
 * 今は代わりに `contact-mail`（メール導線）を出している。
 * この部品もCSS（.lindo-form 一式）も消していないので、実装ができ次第すぐ戻せる。
 *
 * ── 復活のしかた ──────────────────────────────────
 * 1. preview/render.php の
 *      define( 'LINDO_CONTACT_PART', 'contact-mail' );
 *    を 'contact-form-fallback' に戻す（これだけで見た目は戻る）
 * 2. 下の <form> の action を送信先に変える
 *      - Cloudflare Workers（本命）… 同一オリジンの `/api/contact` 等。
 *        Cloudflare へ NS 移管 + Email Routing が前提
 *      - 外部サービス（Formspark 等）… 発行されたエンドポイントURL
 * 3. 外部サービスに出す場合は `_headers` の CSP を直す。
 *    現在 `form-action 'self'` なので、同一オリジン以外への POST は
 *    （CSPが効く配信先に移した時点で）ブロックされる
 * 4. 送信後の遷移先（サンクスページ）と、迷惑メール対策（honeypot 等）を決める
 * 5. 実際に送信して**受信できることを確認**してから公開する
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
