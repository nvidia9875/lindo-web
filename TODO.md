# LINDO サイト TODO

最終更新: 2026-07-16

- 本番: https://nvidia9875.github.io/lindo-web/ （microCMS → GitHub Actions → GitHub Pages）
- 関連: `HANDOVER.md`（引き継ぎ手順）/ `MICROCMS-SCHEMA.md`（スキーマ定義）/ `CLAUDE.md`（構成）

---

## いま何が動いているか

```
microCMS（サカイさんのアカウント・サービスID lindo）
   │  ★ここを先方アカウントに移すのが最優先
   ↓
GitHub Actions（.github/workflows/deploy.yml）
   │  php preview/render.php でHTML生成 → 健全性チェック
   ↓
GitHub Pages（nvidia9875.github.io/lindo-web）
```

- **サイトにはmicroCMSに入っているものだけが出る。現在 SugarNote 1組のみ。**
- `preview/index.html` は**CIが生成**する。手でコミットしない。
- 旧方式（`works-img/` のローカル走査・9組）は退避用に残してある。Secretを外せば戻る。

---

## ★次にやること（この順番）

### 1. microCMS を先方アカウントで作り直す ← 最優先

**なぜ今か: まだ1組しか入っていないから。** 先に8組入れると、引き継ぎ時に**235枚を2回手入力**するか移行スクリプトを書くはめになる。器を先に移せば入力は1回で済む。

- [ ] **伊藤さんにmicroCMSアカウントを作ってもらう**（Hobby・無料・クレカ不要）
      … 所有者が先方である必要があるので本人にしか作れない
- [ ] サカイさんをメンバーに招待してもらう（3名まで無料）
- [ ] スキーマをエクスポート → 新サービスでインポート（**手作業で作り直さない**）
- [ ] GitHubの Variable `MICROCMS_SERVICE_ID` と Secret `MICROCMS_API_KEY` を差し替え（**コードは無改修**）
- [ ] 旧サービスのAPIキーを失効

手順の詳細は `HANDOVER.md`。

### 2. 残り8組を投入

**1が終わってから。** SEVENTEEN / LE SSERAFIM / TOMORROW X TOGETHER / NMB48 / BMSG / 高嶺のなでしこ / OCTPATH / No No GIRLS。

- 画像は `wp-theme/preview/works-img/<アーティスト>/` に最適化済みのものがある（初回投入はこれでよい）
- 作品の分け方・タイトル・担当・MVリンクは `wp-theme/preview/content-manifest.php` が正
- **画像はそのまま上げてよい**（長辺1280超は配信時に自動縮小。加工不要）
- 重複を入れるとビルドで警告が出る（実際に05.webpの重複が起きた）

### 3. お問い合わせフォーム ← 公開前に必須

**現状のフォームは押しても何も送信されないダミー。** このまま公開すると問い合わせを取りこぼす。

**方針: Cloudflare Workers（決定済み・2026-07-16）**

- 検証済み宛先への送信は**全プラン無料**、送信上限にもカウントされない
- **ただし Cloudflare Email Routing が前提 ＝ styledbylindo.com を Cloudflare に載せる必要がある**（下記4と同時）
- CSPが `form-action 'self'` なので、`workers.dev` の別ドメインではなく**同一オリジン**で動かすこと

**未決:** ドメイン移管まで待つか、暫定で Formspark（$25買い切り・5万通・ドメイン移管不要・`<form action>`だけで動く）を挟むか。

### 4. 独自ドメイン + Cloudflare への移行

新サイトを `nvidia9875.github.io` のままにはしないので、いずれ必ず通る。3のフォームもこれに乗る。

**⚠️ 事前調査済み・慎重にやること（失敗すると先方の業務が止まる）**

| 現状 | 値 |
|---|---|
| ドメイン | **ムームードメイン**（GMOペパボ）。ネームサーバー `dns01/02.muumuu-domain.com` |
| A | `103.169.142.0`（ロリポップ）。**現行サイトが生きている**（`<title>LINDO Co.,Ltd.`） |
| **MX** | **`50 mx01.lolipop.jp`** ← **contact@styledbylindo.com はここで受けている。壊すとメールが止まる** |
| TXT | Canva認証 / Google Site Verification ← これも引き継ぎが必要 |
| TXT `_dmarc` | `v=DMARC1; p=none;` |
| SPF | **無し**。移管後もメール送信を続けるなら別途検討 |

⚠️ **現行サイトとメールは同じロリポップ契約に同居。** 解約するとメールも消える（`MTG-HANDOVER.md` Part 4 で確認）。

- [ ] Cloudflareアカウント作成（**先方名義が安全**。サカイさん個人だと離任時に全部止まる）
- [ ] ゾーン追加時に **MX・TXTを含む既存レコードを完全に移す**（ネームサーバー切替はその後）
- [ ] Cloudflare Pages（または Workers Static Assets）へ配信を移す
      → **`_headers` が効くようになり、現状死んでいるCSPが復活する**
- [ ] Email Routing で `contact@styledbylindo.com` を検証済み宛先に登録 → 3のフォームが動く
- [ ] 現行サイトからの切替タイミングを先方と合意

### 5. Webhook で自動反映（任意）

入れると伊藤さんが「公開」を押すだけでサイトが更新される。未設定なら手動ビルド。

- [ ] GitHub の Fine-grained PAT を作成
- [ ] microCMS の Webhook → `POST https://api.github.com/repos/.../dispatches` / `{"event_type":"microcms-update"}`
- ワークフロー側は `repository_dispatch` で受ける口を**設置済み**

---

## 積み残し

### ~~Business Partner / セクション文言の編集~~ — ✅ **実装済み（2026-07-26）**

**サイトの文言はすべて microCMS から編集できる。** 開発者を通さずに直せることが要件だった。

- `site`（オブジェクト形式API）1本に集約。仕様は `MICROCMS-SCHEMA.md`「2.5」
- 既定値 = 現在の表示内容は `wp-theme/lindo/inc/site-defaults.php`（**template-parts に文言を直書きしないこと**）
- **API 未作成でも動く**。既定値で描画され、ビルドログに警告が出るだけ
- 空欄の項目は既定値のまま＝全消ししてもサイトは壊れない
- 固定のまま残したもの: ロゴ文字4箇所（ロゴSVG支給時に差し替え）／セクション番号（自動採番）／ナビのリンク先／装飾のピンクの「.」

**改行の規則**（先方への説明もこの通り）: 改行1つ = `<br>` / 空行1つ = 段落。
見出し `aboutHeading` だけ例外で、改行 = 「折り返してよい位置」（`<wbr>`）。CSS が `word-break: keep-all` のため、指定が無いと日本語が文節を無視して切れる。

### T7: 画像ごとの表示位置 — 保留

現在は一律 `--img-pos: 50% 30%`（CSS変数）。素材の約7割が縦長で、中央基準だと顔が切れるための暫定対応。

- 個別指定するなら `<img style="--img-pos: 50% 10%">` を出すだけでCSS側は対応済み
- ただし microCMS の「複数画像」は**画像ごとのメタを持てない**。やるなら `繰り返し(images) → カスタム(image) = {画像, focal_x, focal_y}` になり、一括アップロードの快適さと引き換えになる
- **入れ直した実物を見てから判断**（2026-07-15 に「一旦今のままでよい」と決定）

### ロゴ / favicon — 支給待ち

- ロゴは現在**テキスト** `LIND<b>O</b>` が4箇所（ヘッダー / ローダー / フッター / ヒーローのラベル）
- ロゴ到着時に**サイト全体のフォント再選定**もセット（現在の Archivo / Zen Kaku Gothic New はプレースホルダ）
- favicon は**存在しない**。ロゴから作る

### 旧方式の撤去 — 全組投入後

- `real-data.php` / `content-manifest.php` / `build-works-img.php` / `works-img/`
- WordPressテーマ一式（`wp-theme/lindo/inc/` の大半、`front-page.php`、`functions.php` 等）
  ※ `template-parts/` と `assets/` は**現役**（静的サイトが描画に使っている）。消さないこと

---

## 先方 / 伊藤さん待ち

- **microCMSアカウントの作成** ← 上記1の前提。最優先
- 画像の並び順（`01.jpg`…） / アーティストの並び順
  - microCMS では**作品の順番・写真の順番はドラッグ**で変えられる。番号付けが要るのは初回投入時だけ
  - アーティストの順番は `order`（数字）の昇順
- **ロゴデータ**
- フォームを暫定対応するか、ドメイン移管まで待つか

## 所有権の整理（**決定済み・2026-07-26**）

**GitHubリポジトリ以外は、すべて株式会社LINDO名義で持つ。**

| | 現在 | 名義 |
|---|---|---|
| microCMS | サカイさん | **→ LINDO**（上記1） |
| Cloudflare | 未作成 | **LINDO名義で作成** |
| ドメイン | ムームードメイン | **LINDO**（登録者名を要確認） |
| ロリポップ（現サイト＋メール） | — | **LINDO**（契約者を要確認） |
| GitHubリポジトリ | `nvidia9875`（個人） | サカイ保持。**例外はここだけ** |

---

## 完了済み（記録）

| | 内容 | コミット |
|---|---|---|
| T1 | ヒーロー文言（読点削除＋改行） | `d4b0607` |
| T2 | **Safari で Works が出ない** … 原因はSafariではなく「背の高い要素×IntersectionObserverのthreshold」。Worksのラッパーが4949pxあり `threshold:0.14` は vh≥753px を要求。iPhoneのURLバー展開時(660-735px)は永久に発火しなかった | `d4b0607` |
| T3-A | **ライトボックスでPCで画像の下が切れる** … `.lb-figure` のグリッド行がautoで `max-height:100%` が無効化され原寸描画。1440x900で430px、1280x800で525px切れていた | `d4b0607` |
| T3-B | 画像のトリミング … `object-position` が未設定で全て中央基準だった。`--img-pos: 50% 30%` を導入。SPモーダルの `16/11` は縦長素材に対し平均40%欠損だったため 4/5 に統一 | `d4b0607` |
| T7.5 | **作品グループの本番対応** … WordPressでは実装が必要だったが、**microCMS採用により解決**（繰り返し→カスタム→複数画像の入れ子で表現） | — |
| — | microCMSデータ層（`real-data.php` と同じ配列を返す＝template-parts無改修） | `3aaf5aa` |
| — | CIビルド＆配信（Actions） | `6312a39` |
| — | 画像重複の検知 / 引き継ぎ手順 / CLAUDE.md更新 | `99cb18c` |
| ~~T6~~ | ~~Business Partner の Customizer 対応~~ … **WPをやめたため無効。上記「積み残し」参照** | `ba801b3` |
