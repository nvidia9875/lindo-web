# 公開までにやること（全量）

作成: 2026-08-10（最終引き継ぎ日）

**この日の方針（決定済み）**

| 論点 | 決定 |
|---|---|
| ドメイン切替 | **今日はやらない。**9組の投入が終わってから |
| 公開時のコンテンツ | **9組そろえてから**切替 |
| お問い合わせフォーム | **mailto 表示のみに変更**（本実装は後日） |
| microCMS の名義 | **今日 LINDO 名義へ移管する** |

> ⚠️ `MTG-HANDOVER.md`（2026-07-26 付）の Part 1〜4 は**実行されていない**。
> 本ファイルが現状に基づく最新の残タスクで、以後こちらを正とする。

---

## 0. 実測した現状（2026-08-10・**着手前**）

> この表は作業を始める前のスナップショット。🔴 のうちコード側は下記「完了した分」で解消済み。

| 項目 | 状態 |
|---|---|
| 未コミット作業 | 🔴 **23ファイル・約660行が未コミット・未push**。このMacにしか存在しない |
| microCMS 名義 | 🔴 サカイ名義 `lindo` のまま（GitHub Variable も 07-15 から未変更） |
| microCMS の中身 | 🔴 **SugarNote 1組のみ**（残り8組・約235枚が未投入） |
| `site` API（文言編集） | 🔴 **未作成（HTTP 404）** — 先方が文言を直せない状態 |
| 本番サイト | 2026-07-23 のビルド。旧title・noindex・1組 |
| DNS | 手つかず。NS=ムームー / A=`103.169.142.0`(ロリポップ) / MX=`50 mx01.lolipop.jp` |
| お問い合わせフォーム | 🔴 `action="#"` のダミー。押しても送信されない |
| 配信内容 | 🔴 **内部ドキュメントが全部公開中**（下記 2-1） |
| サイト本体のURL | `/wp-theme/preview/index.html`。ルートは meta refresh リダイレクト |
| canonical / og:url | 未設定 |
| favicon | 無し |
| sitemap.xml | 架空のWORKS 3件のまま |

補足: MX の TTL は 3600秒（DNSの切り戻しは1時間）。旧サイトは1ページ構成のため、切替時の 404 リスクはほぼ無い。

---

---

## ✅ 2026-08-10 に完了した分（サカイ・MTG前）

| | 内容 | コミット |
|---|---|---|
| 1 | 未コミットだった `site` API 対応一式（23ファイル）をコミット | `3309f32` |
| 2-1 | **内部ドキュメントの公開を停止**。配信物を `dist/` に明示的に絞る方式へ | `ef3ad6f` |
| 2-2 | サイト本体をルートへ（中継リダイレクトを廃止） | `ef3ad6f` |
| 2-3 | お問い合わせをメール導線に（**フォームは1行で戻せる形で保持**） | `ef3ad6f` |
| 2-4 | canonical / og:url / sitemap.xml / robots.txt / 404ページ | `ef3ad6f` |
| 2-5 | 暫定 favicon | `ef3ad6f` |
| 3準備 | `site` API のマージ処理を検証（上書き・空欄・noindex・繰り返し・空行除去） | — |
| 3準備 | `MICROCMS-SITE-INPUT.md`（入力シート）を既定値から自動生成 | — |
| 3準備 | **`microcms-schema/` にインポート用スキーマJSONを用意**（`artists` は実エクスポート、`site` は手組み32フィールド）。コードが読むIDと過不足なく一致することを自動照合済み | — |
| — | `MTG-2026-08-10.md`（当日の進行表）を作成 | — |

**push・CI・本番反映まで完了。** 内部ドキュメントが 404 になったことを本番で実測確認済み。

**今日これ以上サカイ単独で進められる作業は無い。** 残りはすべて
「伊藤さんのアカウント作成 → メンバー招待」が前提になる。

---

# ■ 今日やること

## 1. コードを確定させる ★最優先

**これをやらないと引き継ぎが成立しない。**サイト文言を先方が編集できるようにする実装一式（`site` API 対応）が、コミットもpushもされておらず**この開発機にしか存在しない**。

- [x] 1-1. PHP 構文チェック
      ```
      cd /Users/shun/Desktop/lindo
      for f in $(git status --porcelain | awk '{print $2}' | grep '\.php$'); do php -l "$f"; done
      ```
- [x] 1-2. ローカルでビルドして目視確認
      ```
      cd /Users/shun/Desktop/lindo/wp-theme
      set -a; . ../.env.local; set +a
      php preview/render.php > preview/index.html
      php -S 127.0.0.1:8745 -t .
      ```
- [x] 1-3. コミット & push（CIが自動で走る）
- [x] 1-4. Actions の成功と本番の表示を確認
      ```
      gh run watch --repo nvidia9875/lindo-web
      ```

> ⚠️ `wp-theme/preview/index.html` は**CIが生成する**。手でコミットしないこと。

**新規追加されるファイル**
- `/Users/shun/Desktop/lindo/wp-theme/lindo/inc/site-defaults.php` — 文言の既定値（＝microCMSへの初期投入原本）
- `/Users/shun/Desktop/lindo/wp-theme/preview/site-data.php` — `site` API からの取得・マージ
- `/Users/shun/Desktop/lindo/wp-theme/preview/microcms-client.php` — API クライアント
- `/Users/shun/Desktop/lindo/HANDOVER-LINDO.md` — 先方向け運用ガイド

---

## 2. 公開できる作りに直す（コード作業）

ドメイン切替は後日だが、**切替の前提になる作業**なので今日のうちに済ませる。

### 2-1. 内部ドキュメントを配信から外す 🔴 最優先

現在、以下がすべて **HTTP 200 で誰でも読める**。ドメインを切り替えれば `styledbylindo.com/MTG-HANDOVER.md` として公開される。

| 公開中のURL | 中身 |
|---|---|
| `/MTG-HANDOVER.md` | **DNSレコード全表・Google/Canva検証トークン・内部の名義議論** |
| `/HANDOVER.md` | 移行手順・DNS情報 |
| `/TODO.md` | 残作業・内部メモ |
| `/CLAUDE.md` | 開発メモ |
| `/README.md` | 同上 |
| `/gallery.html`, `/works/` | 旧デモ・架空の作品データ |

原因は `.github/workflows/deploy.yml` の `upload-pages-artifact` が `path: .`（リポジトリ全体）になっていること。

- [x] 2-1-1. ワークフローに `dist/` を組み立てるステップを追加し、**配信するものだけ**コピーする
      配信するもの: 生成した `index.html` / `wp-theme/lindo/assets/` / `404.html` / `robots.txt` / `sitemap.xml` / `_headers` / `.nojekyll`（+ 後日 `CNAME`, `favicon.ico`）
- [x] 2-1-2. `upload-pages-artifact` の `path` を `dist` に変更
- [x] 2-1-3. デプロイ後、`/MTG-HANDOVER.md` 等が 404 になることを確認

### 2-2. サイト本体をルートに移す

独自ドメインのトップが meta refresh のリダイレクトページなのは不可（SEO・体感速度・共有時の見え方）。

- [x] 2-2-1. `render.php` の `LINDO_URI` を出力先に応じて切り替える
      （現在 `'../lindo'` 固定＝`preview/` 配下前提。ルート出力なら `wp-theme/lindo`）
      → `/Users/shun/Desktop/lindo/wp-theme/preview/render.php:23`
- [x] 2-2-2. ワークフローの出力先を `dist/index.html` に変更
- [x] 2-2-3. ルートの `/Users/shun/Desktop/lindo/index.html`（リダイレクト用）を廃止

### 2-3. お問い合わせフォームを mailto に置き換え

現状 `action="#"` で**押しても何も起きない**。このまま公開すると問い合わせを取りこぼす。

- [x] 2-3-1. `contact-form-fallback` の代わりに、メールリンクのCTAを出す部品を用意
      現行: `/Users/shun/Desktop/lindo/wp-theme/lindo/template-parts/contact-form-fallback.php`
      呼び出し元: `/Users/shun/Desktop/lindo/wp-theme/preview/render.php:41`
- [x] 2-3-2. 宛先は `$site['contact']['email']`（既定 `contact@styledbylindo.com`）を使う。**直書きしない**
      → `/Users/shun/Desktop/lindo/wp-theme/lindo/inc/site-defaults.php`
- [x] 2-3-3. `section-contact.php` の `.direct` と重複しないようレイアウトを整える
      → `/Users/shun/Desktop/lindo/wp-theme/lindo/template-parts/section-contact.php:26-33`
- [x] 2-3-4. 1440 / 768 / 375 で崩れていないか確認

### 2-4. SEO まわり

- [x] 2-4-1. `render.php` に `canonical` と `og:url` を追加（現在**どちらも無い**）
      → サイトURLを `site-defaults.php` に `siteUrl` として持たせ、microCMS からも編集可にする
- [x] 2-4-2. `sitemap.xml` を作り直す（現在は架空のWORKS 3件）
      → `/Users/shun/Desktop/lindo/sitemap.xml`
- [x] 2-4-3. `robots.txt` を実態に合わせる（`/concepts/` は配信されなくなるため）
      → `/Users/shun/Desktop/lindo/robots.txt`
- [x] 2-4-4. `404.html` のデザインが旧デモ用（Cormorant Garamond / olive背景）なので現行テーマに合わせる
      → `/Users/shun/Desktop/lindo/404.html`

### 2-5. favicon（暫定）

- [x] 2-5-1. ロゴ未支給のため、**暫定の favicon** を置く（無いとタブが白紙 + `/favicon.ico` が 404）
      ロゴ支給後に差し替える

---

## 3. microCMS を LINDO 名義へ移管（伊藤さん同席）

**投入前にやること。**先に8組入れると 235枚を2回入力することになる。

- [x] 3-1. 【サカイ・事前】現サービス `lindo` の `artists` スキーマを**エクスポート**（カスタムフィールド `work` 含む）
- [ ] 3-2. 【伊藤さん】LINDO名義で microCMS 新規登録 → サービス作成（**Hobby / 無料 / クレカ不要**）
      → 決めたサービスIDをメモ: `____________`
- [ ] 3-3. 【伊藤さん】サカイをメンバー招待（3名まで無料）
- [ ] 3-4. 【サカイ】API「アーティスト」（**リスト形式** / `artists`）を作成
      → APIスキーマ **インポート**: `/Users/shun/Desktop/lindo/microcms-schema/api-artists.json`
      （カスタムフィールド `work` も同梱。**手打ちしない** — IDを打ち間違えると写真が0枚になるがエラーは出ない）
- [ ] 3-5. 【サカイ】API「サイト設定」（**オブジェクト形式** / `site`）を**新規作成**
      ⚠️ **現サービスにも存在しない（404）。今日つくる必要がある**
      - APIスキーマ **インポート**: `/Users/shun/Desktop/lindo/microcms-schema/api-site.json`（32フィールド + カスタムフィールド `service`）
      - 失敗したら `api-site-minimal.json` → `noindex` と `ogImage` を手で追加
        （詳細: `/Users/shun/Desktop/lindo/microcms-schema/README.md`）
      - 初期値: `/Users/shun/Desktop/lindo/MICROCMS-SITE-INPUT.md` からコピー
      - `noindex` は **ON のまま**（公開日に OFF）
      - **公開する**
- [ ] 3-6. 【両】SugarNote を新サービスへ投入 → **公開**（伊藤さんに操作を体得してもらう）
      画像: `/Users/shun/Desktop/lindo/wp-theme/preview/works-img/SugarNote/`
- [ ] 3-7. 【サカイ】新サービスの**取得用(GET) APIキー**を発行
- [ ] 3-8. 【サカイ・ターミナル.appで】鍵を差し替え（`!` で実行しない＝会話ログに残さない）
      ```
      gh variable set MICROCMS_SERVICE_ID --repo nvidia9875/lindo-web --body "<新サービスID>"
      gh secret   set MICROCMS_API_KEY    --repo nvidia9875/lindo-web
      ```
- [ ] 3-9. 【サカイ】ビルドして本番確認
      ```
      gh workflow run deploy.yml --repo nvidia9875/lindo-web
      gh run watch --repo nvidia9875/lindo-web
      ```
- [ ] 3-10. **`site` API の疎通を確認**
      マージ処理（上書き / 空欄は既定値 / 未送信は既定値 / `noindex` / 繰り返しの空行除去）は
      ローカルで検証済み。ここで見るのは**実APIとつながるか**だけ:
      文言が microCMS 側の値に変わり、空欄の項目が既定値のまま残っていればOK
- [ ] 3-11. 【サカイ】旧サービス `lindo` の APIキーを失効（**新環境の動作確認後**）
      ※ 旧サービス自体は8組の投入完了まで残しておく

> **順番が命**: 先に鍵を差し替えると、先方CMSが空でビルドが落ちる（空サイトを本番に出さない安全装置）。
> 必ず「新CMSに最低1組入れて公開 → 鍵を差し替え」の順で。

> **切り戻し**: 旧キーを失効する前なら、`MICROCMS_SERVICE_ID` を `lindo` に戻して旧キーを貼れば即復帰できる。

---

## 4. Webhook で自走化

これを入れると伊藤さんが「公開」を押すだけでサイトが更新される。**入れないと投入のたびにサカイへ手動ビルド依頼が発生する。**

- [ ] 4-1. GitHub の **Fine-grained PAT** を作成（対象リポジトリのみ / Contents: Read and write）
- [ ] 4-2. 新サービスの `artists` API → Webhook →「カスタム通知」
      - URL: `https://api.github.com/repos/nvidia9875/lindo-web/dispatches`
      - メソッド: `POST`
      - ヘッダ: `Authorization: Bearer <PAT>` / `Accept: application/vnd.github+json`
      - ボディ: `{"event_type":"microcms-update"}`
      - タイミング: 公開 / 更新 / 削除
- [ ] 4-3. **`site` API 側にも同じ Webhook を設定**（文言を直しても反映されるように）
- [ ] 4-4. 伊藤さんに「公開」を押してもらい、ビルドが自動で走ることを確認
- [ ] 4-5. 伊藤さんに伝える: **反映まで1〜2分かかる**

> ワークフローの受け口（`repository_dispatch: types: [microcms-update]`）は**設置済み**。CMS側の設定だけで有効になる。

---

## 5. コンテンツ投入（9組・約235枚）

**今日始めて、後日完了。**投入が終わるまでドメインは切り替えない。

- [ ] 5-1. 投入の**分担と期限**を決める → `伊藤さん: ______ / サカイ: ______ / 期限: ______`
- [ ] 5-2. 投入内容は `/Users/shun/Desktop/lindo/MTG-HANDOVER.md` の **Part 6** が正（作品名・担当・フォルダ・枚数・MVリンク）
- [ ] 5-3. 画像は `/Users/shun/Desktop/lindo/wp-theme/preview/works-img/<アーティスト>/<作品フォルダ>/` から。**加工不要**
- [ ] 5-4. 同じ画像を2回入れない（ビルドで警告が出る。実際に05.webpの重複が発生した）
- [ ] 5-5. 表示順の最終確認: 1 SEVENTEEN / 2 LE SSERAFIM / 3 TOMORROW X TOGETHER / 4 NMB48 / 5 BMSG / 6 高嶺のなでしこ / 7 OCTPATH / 8 SugarNote / 9 No No GIRLS

---

## 6. 引き継ぎ物・確認事項（伊藤さんが居ないと進まないもの）

### 6-1. 渡す

- [ ] `/Users/shun/Desktop/lindo/HANDOVER-LINDO.md` の**冒頭の空欄を埋める**（サービスID / サイトURL）→ 先方へ共有
- [ ] コード一式を zip で Google Drive へ納品（GitHubを個人保持にする代わりの担保）

### 6-2. もらう

- [ ] **ロゴの元データ**（`.ai` / `.svg` / `.pdf`。**PNGだけでは不可**）
      → 到着後、**サイト全体のフォント再選定**と favicon 作成がセットで走る
- [ ] Business Partner の企業リスト（現在10社。増減の確認）
- [ ] セクション文言の最終稿（Hero / What We Do / About / Contact）
- [ ] アーティストの紹介文（現在9組ともプロフィール空。不要なら「不要」と決める）

### 6-3. 決める

- [ ] **公開日（旧サイトからの切替日）** → `____________`
- [ ] お問い合わせの**送信先アドレス** → `____________`（`contact@styledbylindo.com` でよいか）
- [ ] フォームの本実装の方式（Cloudflare Workers か 外部サービスか）

### 6-4. 確認（後の移管が詰まる箇所）

- [ ] ムームードメインのアカウント所有者 → `____________`
- [ ] Whois の登録者名が株式会社LINDOか → `____________`
- [ ] ロリポップの契約者 → `____________`
- [ ] **`contact@styledbylindo.com` は送信もしているか、受信だけか** → `受信のみ / 送受信`
- [ ] 他にメールアドレスがないか（`info@` 等） → `____________`

> ⚠️ **現行サイトとメールは同じロリポップ契約に同居している。**
> 新サイト公開後に「もう要らない」と解約すると**メールも同時に消える**。
> 受信のみなら Cloudflare Email Routing（無料）へ移せる。送信もしているなら解約不可。

---

# ■ 後日（公開日）

## 7. ドメイン切替 — ムームーで A レコードだけ差し替える

**推奨方式。ネームサーバーはムームーのまま、A/CNAME だけ GitHub Pages に向ける。**
MX・TXT に一切触らないので**メール事故のリスクがゼロ**。TTL 3600秒＝切り戻しは1時間。

- [ ] 7-1. 9組の投入が完了していることを確認（**これが切替の前提**）
- [ ] 7-2. `dist/` に `CNAME` ファイル（内容 `styledbylindo.com`）を含める
- [ ] 7-3. GitHub → Settings → Pages → Custom domain に `styledbylindo.com` を設定
- [ ] 7-4. ムームードメインの DNS を変更
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
      | **TXT** | `@` | google-site-verification / canva-domain-verify | 🔴 **触らない** |
      | **TXT** | `_dmarc` | `v=DMARC1; p=none;` | 🔴 **触らない** |
      | **NS** | `@` | `dns01/02.muumuu-domain.com` | 🔴 **触らない** |
- [ ] 7-5. HTTPS証明書の発行を待つ（数分〜1時間）→ **Enforce HTTPS を ON**
- [ ] 7-6. 検証
      ```
      dig +short styledbylindo.com A          # → 185.199.x.153 が4本
      dig +short styledbylindo.com MX         # → 50 mx01.lolipop.jp（変化なし）
      curl -sI https://styledbylindo.com | head -3
      ```
- [ ] 7-7. **`contact@styledbylindo.com` へテストメールを送受信**（最重要の確認）
- [ ] 7-8. 🔴 **ロリポップは解約しない**（メールが同居している）

## 8. 検索エンジンへの公開

- [ ] 8-1. microCMS「サイト設定」→ **「検索エンジンに載せない」を OFF** → 公開
- [ ] 8-2. Google Search Console でサイトマップを送信（TXT検証は残っているので所有権はそのまま）
- [ ] 8-3. 旧サイトのインデックスが新サイトに置き換わるのを確認

## 9. お問い合わせフォームの本実装

mailto は暫定。フォームを復活させるなら:

- **Cloudflare Workers**（本命）… 送信無料・同一オリジンでCSP適合。ただし **Cloudflare へ NS移管が前提**（Email Routing が必要）
- **外部サービス**（Formspark $25買い切り等）… 移管不要ですぐ動く。`_headers` の `form-action 'self'` の修正が必要

## 10. Cloudflare への移行（任意・CSPを効かせたい場合）

GitHub Pages はレスポンスヘッダを設定できないため、`_headers` の CSP は**現状も移行後も効かない**。
Cloudflare Pages に移すと有効化される。**Pagesプロジェクトは必ず「Direct Upload」で作ること**
（CloudflareのビルドイメージにPHP 8.3が無く、Git連携ではビルドできない。かつ Git連携で作ると後から変更できない）。

## 11. ロゴ支給後

- [ ] ロゴ差し替え（ヘッダー / ローダー / フッター / ヒーローのラベル の4箇所）
- [ ] **サイト全体のフォント再選定**（現在の Archivo / Zen Kaku Gothic New は仮）
- [ ] favicon を本作成

## 12. 旧方式の撤去（全組投入後）

- [ ] `real-data.php` / `content-manifest.php` / `build-works-img.php` / `works-img/`
- [ ] WordPressテーマ一式（`inc/` の大半 / `front-page.php` / `functions.php`）
- [ ] 🔴 `template-parts/` と `assets/` は**現役**（静的サイトが描画に使用）。消さないこと

---

## 積み残し（判断保留）

### 画像ごとの表示位置

一律 `--img-pos: 50% 30%`。素材の約7割が縦長で、中央基準だと顔が切れるための暫定対応。
個別指定するなら `<img style="--img-pos: 50% 10%">` を出すだけでCSSは対応済みだが、
microCMS の「複数画像」は画像ごとのメタを持てないため、`繰り返し → カスタム = {画像, focal_x, focal_y}` に変える必要がある
（＝一括アップロードの快適さと引き換え）。**9組を実際に入れた画面を見てから判断**。
