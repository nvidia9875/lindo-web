# microCMS スキーマ仕様（T4 用）

`preview/content-manifest.php` ＋ `preview/real-data.php` を microCMS に置き換えるための設計。
**アカウント作成後、この通りに作って「検証チェックリスト」を上から潰す。**

- 前提: microCMS Hobby（無料・クレカ不要・3名まで）
- ゴール: `real-data.php` が返す**配列の形を1ミリも変えずに**データ源だけ差し替える
  → `template-parts/` 12本・`lindo.css`・JS5本は**無改修**

---

## 1. 守るべき契約（ここが変わると全部壊れる）

`real-data.php` の戻り値。`section-artists` / `artist-card` / `artist-modal` がこの形に依存している。

```php
// artist
[
  'id'       => 1,                    // 導出（連番）
  'slug'     => 'artist-1',           // 導出
  'index'    => '01',                 // 導出（%02d）
  'name'     => 'SEVENTEEN',
  'name_sub' => '',
  'role'     => 'Style Direction',
  'tags'     => ['Style', 'Direction'], // 導出（role を / 、 , で分割）
  'profile'  => ['段落1', '段落2'],      // 導出（空行で分割）
  'portrait' => $image,               // 導出 = works[0]['cover']
  'gallery'  => [$image, ...],        // 導出 = 各 work の cover を集めたもの
  'works'    => [$work, ...],
  'links'    => [['label'=>..., 'url'=>...], ...],
]

// work
[
  'slug'    => 'work-1-1-shohikigen', // 導出
  'title'   => 'JP 4th Single「消費期限」',
  'role'    => 'Style Direction',
  'url'     => '',        // 任意。入れると MV 等の外部リンクタイルになる
  'cover'   => $image,    // 導出 = gallery[0]
  'gallery' => [$image, ...],
]

// image
[ 'url' => '...', 'w' => 863, 'h' => 1280, 'alt' => 'SEVENTEEN JP 4th Single「消費期限」' ]
```

**重要: 導出項目は microCMS にフィールドを作らないこと。** PHP 側で組み立てる。
先方に無駄な入力欄を見せないためであり、`slug`/`index` のような内部値を人手で管理させると必ずズレる。

| 項目 | 出どころ |
|---|---|
| `id` / `slug` / `index` | 並び順から導出 |
| `portrait` | `works[0]['gallery'][0]` |
| `gallery`（artist階層） | 各 work の先頭画像を集めたもの |
| `cover`（work階層） | その work の `gallery[0]` |
| `w` / `h` | **microCMS の画像フィールドが `width`/`height` を返す**（現行の `getimagesize` は不要になる） |
| `alt` | `name . ' ' . title` で導出（現行 `real-data.php:82` と同じ） |
| `tags` | `role` を `/` `、` `,` で分割（本番 `inc/artist-data.php:36` と同じ規則） |

---

## 2. 作るもの

### API: `artists`（リスト形式）

| フィールドID | 表示名 | 種類 | 必須 | 備考 |
|---|---|---|---|---|
| `name` | アーティスト名 | テキストフィールド | ✓ | |
| `nameSub` | サブ表記 | テキストフィールド | | 現状どのアーティストも未使用 |
| `role` | 担当 | テキストフィールド | | 例: `Style Direction`。`/` 区切りでタグに分解される |
| `profile` | プロフィール | テキストエリア | | 空行で段落が分かれる |
| `order` | 表示順 | 数字 | ✓ | **下記「並び順」参照** |
| `works` | 作品 | 繰り返しフィールド → `work` | ✓ | |
| `links` | 外部リンク | 繰り返しフィールド → `link` | | 現状未使用。将来用 |

### カスタムフィールド: `work`

| フィールドID | 表示名 | 種類 | 必須 | 備考 |
|---|---|---|---|---|
| `title` | 作品名 | テキストフィールド | ✓ | 例: `Artist Photo（ピンク背景）` |
| `role` | 担当 | テキストフィールド | | 例: `Creative Produce` |
| `gallery` | 写真 | **複数画像** | ✓ | **★これが検証対象** |
| `url` | 外部リンク | テキストフィールド | | **入れるとギャラリーではなく ▶ 付きリンクタイルになる**（MV等）。先頭画像がサムネになる |

### カスタムフィールド: `link`

| フィールドID | 表示名 | 種類 |
|---|---|---|
| `label` | 表示名 | テキストフィールド |
| `url` | URL | テキストフィールド |

---

## 2.5 API: `site`（オブジェクト形式）— サイト文言

**目的: 開発者を通さずに文言を直せるようにする。** これが無いと「ヒーローの一行を変えたい」だけでコード修正の依頼が発生する。

- 形式: **オブジェクト形式**（1件だけのAPI。リストではない）
- エンドポイント: `site`
- **未作成でも動く**。その場合は `wp-theme/lindo/inc/site-defaults.php` の既定値で描画され、ビルドログに警告が出るだけ
- **空欄の項目は既定値のまま**。全消ししてもサイトは壊れない

### 改行の規則（先方への説明もこの通りに）

| 打ち方 | 結果 |
|---|---|
| 改行1つ | その位置で改行（`<br>`） |
| 空行1つ | 段落が変わる（`<p>` が分かれる） |
| **見出し（`aboutHeading`）だけ例外** | 改行 = **「折り返してよい位置」**（`<wbr>`）。必ず折り返すわけではない |

> 見出しが例外なのは、CSS の `word-break: keep-all` で「指定した位置以外では折り返さない」ためです。指定が無いと日本語が文節を無視した位置でぶつ切りになります。

### フィールド一覧

| フィールドID | 表示名 | 種類 | 備考 |
|---|---|---|---|
| `siteTitle` | ページタイトル | テキスト | ブラウザのタブ／検索結果の見出し |
| `siteDescription` | ページの説明 | テキストエリア | 検索結果／SNS共有の説明文 |
| `noindex` | 検索エンジンに載せない | 真偽値 | **公開時にOFFにする**。ONの間はGoogleに出ない |
| `ogImage` | SNS共有画像 | 画像 | 推奨 1200×630 |
| `loaderSub` | ローダーの小文字 | テキスト | 起動時ロゴの下 |
| `heroLabel` | ヒーロー：社名表記 | テキスト | 例 `LINDO Co., Ltd.` |
| `heroLabelStrong` | ヒーロー：社名の後ろ（太字） | テキスト | 例 `Visual Creative`。空なら「—」ごと消える |
| `heroMeta` | ヒーロー：右上 | テキストエリア | 例 `Tokyo, JP` / `Creative Studio` |
| `heroLine1` | 大見出し1行目 | テキスト | 例 `VISUAL` |
| `heroLine2` | 大見出し2行目 | テキスト | 例 `CREATIVE`。**末尾のピンクの「.」は自動。入力しない** |
| `heroLead` | ヒーロー：リード文 | テキストエリア | |
| `heroTags` | ヒーロー：右下タグ | テキストエリア | |
| `aboutLabel` | Aboutの見出し語 | テキスト | **ナビの表示名も兼ねる** |
| `aboutHeading` | Aboutの大見出し | テキストエリア | 改行＝折り返してよい位置（上記） |
| `aboutBody` | Aboutの本文 | テキストエリア | |
| `repName` | 代表者名 | テキスト | 空にすると代表ブロックごと消える |
| `repTitle` | 代表者の肩書 | テキスト | |
| `repProfile` | 代表者プロフィール | テキストエリア | 空行で段落 |
| `serviceLabel` | What We Doの見出し語 | テキスト | ナビの表示名も兼ねる |
| `services` | 事業内容 | 繰り返し → `service` | **連番（01,02…）は自動**。入力しない |
| `worksLabel` | Worksの見出し語 | テキスト | ナビの表示名も兼ねる |
| `worksLead` | Worksのリード文 | テキストエリア | |
| `partnersLabel` | Business Partnerの見出し語 | テキスト | |
| `partners` | 取引先 | テキストエリア | **1行1社**。空にするとセクションごと消える |
| `contactLabel` | Contactの見出し語 | テキスト | ナビの表示名も兼ねる。**ピンクの「.」は自動** |
| `contactLead` | Contactのリード文 | テキストエリア | |
| `contactEmail` | 問い合わせ用メール | テキスト | Contact とフッターの両方に反映 |
| `companyName` | 会社名（フッター） | テキスト | |
| `companyShortName` | 会社名（コピーライト用） | テキスト | 例 `株式会社LINDO` |
| `companyAddress` | 住所 | テキストエリア | |
| `companyTel` | 電話番号 | テキスト | 表示用の形（`03-5308-5822`）。**リンクは自動生成** |
| `companyNote` | コピーライト右の文言 | テキスト | 例 `Visual Creative Studio` |

### カスタムフィールド: `service`

| フィールドID | 表示名 | 種類 |
|---|---|---|
| `title` | 見出し | テキストフィールド |
| `description` | 説明 | テキストエリア |

> 見出しが空の行は表示されない（連番がズレないように捨てる）。

### 初期値の入れ方

`wp-theme/lindo/inc/site-defaults.php` が**現在サイトに出ている文言そのもの**。
新規作成時はここから写せば、見た目を変えずに「編集できる状態」に移行できる。

### 編集できないもの（意図的に固定）

| | 理由 |
|---|---|
| ロゴ文字（LINDO）4箇所 | ロゴSVG支給時にまとめて差し替える前提。中途半端に可変にしても混乱するだけ |
| セクション番号（01〜04） | 構造。並びを変えたら番号もズレるべきなので自動 |
| ナビのリンク先（`#about` 等） | 構造。表示名だけ編集可 |
| ピンクの「.」（CREATIVE. / Contact.） | 装飾マークアップ。入力させると壊せてしまう |
| お問い合わせフォームの項目 | フォーム実装（未着手）と一体。実装時に再検討 |

---

### 並び順について

- **作品の順番・写真の順番**: 繰り返しフィールドの行、複数画像の中身、いずれもドラッグで並べ替えられる（公式ドキュメントに記載あり）
- **アーティストの順番**: リスト形式APIを管理画面でドラッグ並べ替えできるかは**未確認**。
  → できない前提で `order`（数字）を持たせ、取得時に `orders=order` で並べる。確実に動く。
  → 検証で「ドラッグできる」と分かれば `order` は削ってよい

---

## 3. ★検証チェックリスト

**サービスID: `lindo`（https://lindo.microcms.io）— 2026-07-15 作成済み**

- [x] **1. 繰り返しフィールド（works）の中のカスタムフィールド（work）の中に「複数画像」が置けるか**
  - ✅ **2026-07-15 検証OK。** カスタムフィールドの種類一覧に「複数画像」があり、繰り返しフィールドから `作品` を参照できた。
  - → **microCMS 案で確定。Sveltia への切り替えは不要になった。**（ドキュメントで確認できなかった唯一の不確実性がこれだった）
- [x] **2. SugarNote を再現できるか**（最難関ケース）— ✅ **2026-07-15 OK**
  - 作品1: `Artist Photo（ピンク背景）` 画像11枚
  - 作品2: `Artist Photo（外撮影）` 画像7枚
  - 作品3: `「嘘だよ」MV` 画像1枚 ＋ `url = https://youtu.be/lRI7AdFnMDk`
- [x] 3. 複数画像の中身をドラッグで並べ替えられるか — ✅ OK
- [x] 4. 繰り返しフィールドの行をドラッグで並べ替えられるか — ✅ OK
- [ ] 5. アーティスト（リスト）の並び順を管理画面で操作できるか（できなければ `order` で代替）
  - ※アーティストが2件以上にならないと試せない。当面は `order` 前提で進める
- [ ] 6. 画像APIのレスポンスに `width` / `height` が含まれるか（`w`/`h` の供給源）
- [ ] 7. 15MB / 235枚が Hobby の枠に収まるか（ストレージ無制限・転送20GB/月のはず）

---

## 4. 検証が通ったあとの作業

- `real-data.php` を microCMS 取得に差し替え（**戻り値の形は変えない**）
- 画像最適化を imgix API に寄せる（`?w=1280&fm=webp&q=70`）
  → **`sips`（macOS専用）依存が消える＝伊藤さんが自分で画像を追加できるようになる**（これが今回の本題）
- GitHub Actions（`shivammathur/setup-php`）＋ microCMS webhook で自動ビルド
- Cloudflare Pages へ移行（`_headers` が効くので**現状死んでいる CSP が復活する**）
- 既存 235 枚の初回投入（伊藤さんの番号付きフォルダ/ファイル名をそのまま順序に使える）
- WP 側の撤去判断（`artist-cpt.php` / `artist-meta.php` / `artist-data.php` / `company.php` / `partners.php` / `contact.php` / `enqueue.php` / `setup.php` / `front-page.php` / `header.php` / `footer.php` / `functions.php` / `admin-gallery.js`）

## 5. 積み残し

- **お問い合わせフォーム（T5）**: 静的化すると CF7 が使えない。候補は Formspark（$25 買い切り・5万通・`<form action>` だけで動く＝CSPに優しい）か Cloudflare Workers + Email Service（無料）
- **セクションの文言**（Hero / What We Do / About の見出し・Contact の見出し）: 現状は PHP 直書き。microCMS の**オブジェクト形式API**を1本足せば編集可能にできる。範囲は要相談
- **Business Partner**: T6 で Customizer 対応済みだが、WP をやめるなら microCMS 側に移す必要がある（オブジェクト形式APIに `partners` テキストエリア1本で足りる）
- **画像ごとの位置指定（T7・保留中）**: microCMS の「複数画像」は**画像ごとのメタを持てない**。やるなら `繰り返し(images) → カスタム(image) = {画像, focal_x, focal_y}` になり、一括アップロードの快適さと引き換えになる。現状は一律 `--img-pos: 50% 30%` で様子見中
