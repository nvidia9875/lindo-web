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

### 並び順について

- **作品の順番・写真の順番**: 繰り返しフィールドの行、複数画像の中身、いずれもドラッグで並べ替えられる（公式ドキュメントに記載あり）
- **アーティストの順番**: リスト形式APIを管理画面でドラッグ並べ替えできるかは**未確認**。
  → できない前提で `order`（数字）を持たせ、取得時に `orders=order` で並べる。確実に動く。
  → 検証で「ドラッグできる」と分かれば `order` は削ってよい

---

## 3. ★検証チェックリスト（アカウント作成後・30分）

**推奨案はこの1点目の上に乗っている。** ここが通らなければ Sveltia CMS に切り替える（入れ子は明記されているが、伊藤さんに GitHub アカウントと OAuth ブローカーの自前運用が必要になる）。

- [ ] **1. 繰り返しフィールド（works）の中のカスタムフィールド（work）の中に「複数画像」が置けるか**
  - microCMS は「カスタムの中にカスタム」を明確に禁止しており、その回避策として繰り返しフィールドを案内している。状況証拠は強いが**この組み合わせ自体はドキュメントで未確認**
- [ ] **2. SugarNote を再現できるか**（最難関ケース）
  - 作品1: `Artist Photo（ピンク背景）` 画像11枚
  - 作品2: `Artist Photo（外撮影）` 画像7枚
  - 作品3: `「嘘だよ」MV` 画像1枚 ＋ `url = https://youtu.be/lRI7AdFnMDk`
- [ ] 3. 複数画像の中身をドラッグで並べ替えられるか
- [ ] 4. 繰り返しフィールドの行をドラッグで並べ替えられるか
- [ ] 5. アーティスト（リスト）の並び順を管理画面で操作できるか（できなければ `order` で代替）
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
