# 本番切替（styledbylindo.com へ乗り換え）TODO

> ⚠️ **この文書は制作中の作業メモで、内容は古い。**
> 最新は 技術面 → `HANDOVER-DEV.md` / 先方向け運用 → `HANDOVER-LINDO.md`。
> ここは経緯の記録としてのみ残してある。

最終更新: 2026-08-23 ／ 実測ベース

現在公開中の旧サイトを、新サイトに置き換えるまでの作業。**問い合わせ導線を含む。**

---

## 0. いまの状態（実測）

| | |
|---|---|
| 新サイト | https://nvidia9875.github.io/lindo-web/ （10組 / 27作品 / 332枚） |
| データ源 | ✅ **microCMS**（`lindo-web`・LINDO名義）。先方が自分で更新できる |
| 自動反映 | ✅ 10分ごとの定期ビルド（PAT不要） |
| 検索エンジン | 🔒 noindex ON（まだ出ない） |
| 問い合わせ | ⚠️ **メール導線**（届くが、フォームではない） |
| 旧サイト | 稼働中（`<title>LINDO Co.,Ltd.`）。A=`103.169.142.0`（ロリポップ） |
| DNS | NS=ムームー / MX=`50 mx01.lolipop.jp`（8/10から変化なし） |

---

# ■ 切替の前に決めること

- [ ] **公開日** → `____________`
      切り替えた瞬間に**旧サイトは見えなくなる**
- [ ] **問い合わせをフォームにするか** → `フォーム / メール導線のまま`
      → フォームにするなら下記 B を実施
- [ ] **アーティストの紹介文** → `入れる / 不要`
      現在10組とも空欄。モーダルで名前の右が大きく空く

---

# ■ A. 切替そのもの（必須）

## A-1. CNAME を用意する

```bash
cd /Users/shun/Desktop/lindo
echo "styledbylindo.com" > CNAME
```
`scripts/build-site.sh` が dist に含める作りになっている（実装済み）。

## A-2. GitHub Pages にカスタムドメインを設定

https://github.com/nvidia9875/lindo-web/settings/pages
→ Custom domain に `styledbylindo.com`

## A-3. ムームーで DNS を変更 ★ここが本番

https://muumuu-domain.com/checkout/login

| 種別 | 名前 | 値 | 操作 |
|---|---|---|---|
| A | `@` | `185.199.108.153` | **追加** |
| A | `@` | `185.199.109.153` | **追加** |
| A | `@` | `185.199.110.153` | **追加** |
| A | `@` | `185.199.111.153` | **追加** |
| A | `@` | `103.169.142.0` | **削除**（旧サイトが消える） |
| CNAME | `www` | `nvidia9875.github.io` | **追加** |
| A | `www` | `103.169.142.0` | **削除** |
| **MX** | `@` | `50 mx01.lolipop.jp` | 🔴 **絶対に触らない** |
| **TXT** | `@` | google-site-verification / canva-domain-verify | 🔴 触らない |
| **TXT** | `_dmarc` | `v=DMARC1; p=none;` | 🔴 触らない |
| **NS** | — | ムームーのまま | 🔴 触らない |

> ⚠️ ムームーDNS で「ロリポップ連携」から「カスタム設定」に切り替えると、
> **自動生成されていた MX が消えることがある**。変更前に設定画面を全部
> スクリーンショットし、MX を明示的に入れ直すこと。

## A-4. 検証（切替直後）

```bash
dig +short styledbylindo.com A     # → 185.199.x.153 が4本
dig +short styledbylindo.com MX    # → 50 mx01.lolipop.jp が残っているか
curl -sI https://styledbylindo.com | head -3
```

- [ ] **`m.ito@styledbylindo.com` へテストメールを送受信** ★最重要
- [ ] `contact@styledbylindo.com` も同様

## A-5. HTTPS

- [ ] 証明書の発行を待つ（数分〜1時間）
- [ ] GitHub Pages の設定で **Enforce HTTPS** を ON

## A-6. 検索エンジンに載せる

- [ ] microCMS「サイト設定」→ **検索エンジンに載せない を OFF** → 公開
- [ ] Search Console でサイトマップ送信（TXT認証は残るので再認証不要）
      https://search.google.com/search-console

> TTL 3600秒。問題があれば A レコードを戻せば**1時間で復帰**する。

---

# ■ B. 問い合わせフォーム（フォームにする場合）

実装は完了済み。**アクセスキーを1つ設定すればフォームに切り替わる。**

- [ ] B-1. https://web3forms.com/ で受信先メールアドレスを入力 → キーを取得
      ⚠️ **ロリポップ依存のアドレスは避ける**（解約でフォームが死ぬ）
      ⚠️ 入力したアドレスがそのまま届き先。後から変更できるか未確認
- [ ] B-2. キーを登録
      ```bash
      gh variable set WEB3FORMS_ACCESS_KEY --repo nvidia9875/lindo-web --body "<キー>"
      gh workflow run deploy.yml --repo nvidia9875/lindo-web
      ```
- [ ] B-3. **実際に送信して受信できることを確認** ← 飛ばさない
- [ ] B-4. 送信後 `/thanks.html` へ遷移することを確認

> 未設定の間はメール導線が出る。**「押しても届かないフォーム」にはならない**作り。
> 止めたくなったら `gh variable delete WEB3FORMS_ACCESS_KEY` で戻る。

## メールをどうするか（B とは別に要判断）

現在 `m.ito@` と `contact@` はロリポップで動いており、**`m.ito@` は送信にも使用**。

| | 内容 | 手間 |
|---|---|---|
| **A. 名義変更のみ** | メールはそのまま動き続ける | 小 ← **推奨** |
| B. 新ロリポップへ移す | 過去メールの手移行が必要 | 大 |
| C. 別サービスへ | Google Workspace 等 | 中 |

🔴 **ロリポップは解約しない。**サイトが移っても、メールはここに残る。

---

# ■ C. 切替前に済ませたいもの（任意だが推奨）

- [ ] C-1. **アーティストの紹介文**を入れる（原稿をもらう）
- [ ] C-2. **メディアの重複37件を削除**（管理画面から。表示上の問題のみ）
- [ ] C-3. `HANDOVER-LINDO.md` の空欄を埋めて先方へ共有
      - 管理画面: `https://lindo-web.microcms.io/`
      - サイト: 切替後は `https://styledbylindo.com/`
- [ ] C-4. コード一式を zip で納品
- [ ] C-5. Business Partner の企業リスト確認（現在10社）

---

# ■ D. 切替後

- [ ] D-1. **ドメイン・ロリポップの名義移動**（別の方 → 社長）
      🔴 事前にDNS全レコードのスクショ／直後にテストメール
      ※ サイト切替と**別日**にやる。同時だと切り分けできない
- [ ] D-2. `m.ito@` のメールが消える件の解消
      Gmail の「コピーをサーバーに残す」or ロリポップの転送設定
- [ ] D-3. **SPFレコードの追加**（現在未設定。送信メールが迷惑扱いされやすい）
- [ ] D-4. Webhook の再挑戦（PATが直れば即時反映になる。今は10分ごと）
- [ ] D-5. 旧サービス `lindo`（サカイ名義 microCMS）の APIキー失効・削除
- [ ] D-6. 旧方式の撤去（`real-data.php` / `content-manifest.php` /
      `build-works-img.php` / `works-img/` / WPテーマ一式）
      ※ `template-parts/` と `assets/` は現役。消さない

---

## 順番の推奨

```
1. B（フォーム）を先に有効化してテスト送信  ← 切替前に確かめられる
2. C（紹介文・掃除・納品物）
3. A（DNS切替）→ 検証 → HTTPS → noindex OFF
4. D（名義移動など）は落ち着いてから別日に
```

**A-3 の DNS 変更が唯一の不可逆に見える作業**だが、TTL 3600秒なので
A レコードを戻せば1時間で元に戻る。MX に触らない限り、メールは無傷。
