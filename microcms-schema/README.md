# microCMS スキーマ（インポート用）

新しいサービスで API を作るときに**インポートするだけ**で済むよう、スキーマJSONを置いてある。
会議中に何十個もフィールドを手打ちすると、必ず打ち間違いが起きるため。

> **打ち間違いは静かに壊れる。** コードはフィールドIDを名前で直接読んでおり、
> 見つからない項目は「空」として扱う（例外を投げない）。`gallery` を `galery` と
> 打つと、エラーは出ずに写真が0枚になるだけ。だからインポートを使う。

| ファイル | 用途 |
|---|---|
| `api-artists.json` | **03 Works の作品データ**（リスト形式・エンドポイント `artists`）。現サービス `lindo` からの実エクスポート |
| `api-talents.json` | **04 Artists の一覧**（リスト形式・エンドポイント `talents`）。写真＋公式サイトURL |
| `api-site.json` | **サイト設定**（オブジェクト形式・エンドポイント `site`）。32フィールド + カスタムフィールド `service` |
| `api-site-minimal.json` | 上の予備。`noindex` と `ogImage` を抜いた30フィールド版（下記） |

---

## 手順

### 1. アーティスト

1. 新サービスで API を作成
   - 形式: **リスト形式**
   - エンドポイント: `artists`
2. APIスキーマ → **インポート** → `api-artists.json`

カスタムフィールド `work`（`title` / `role` / `gallery` / `url`）も同じJSONに含まれている。

### 2. Artists（04）

サイトの **04 Artists**（写真をクリックすると公式サイトへ飛ぶ一覧）用。

1. API を作成
   - 形式: **リスト形式**
   - API名: `Artists（04・公式サイトリンク）`
   - エンドポイント: **`talents`**
2. APIスキーマ → **インポート** → `api-talents.json`
3. 初期データを入れる（1組・SugarNote）

   ```bash
   cd /Users/shun/Desktop/lindo
   set -a; . ./.env.local; set +a
   php scripts/push-talents.php          # 確認のみ
   php scripts/push-talents.php --write  # 実行
   ```

   写真のアップロードまで自動。**下書き**で入るので、管理画面で「公開」を押す。

> **なぜエンドポイントが `artists` ではなく `talents` なのか**
> `artists` は **03 Works の作品データ**が先に使っている。microCMS はエンドポイント名を
> 重複させられないため、04 のデータは `talents` に入れている。管理画面には
> 日本語のAPI名で並ぶので、先方が取り違えることはない。
>
> | 管理画面の表示 | エンドポイント | サイトのどこ |
> |---|---|---|
> | アーティスト | `artists` | **03 Works**（作品ギャラリー。押すとモーダル） |
> | Artists（04・公式サイトリンク） | `talents` | **04 Artists**（押すと公式サイトへ） |

**API を作らなくてもサイトは壊れない。** その場合はリポジトリ同梱の初期値
（`wp-theme/preview/talents-seed.php` の SugarNote）が表示される。API を作った
時点で、そちらが常に優先される。

### 3. サイト設定

1. 新サービスで API を作成
   - 形式: **オブジェクト形式**（1件だけのAPI。リストではない）
   - エンドポイント: `site`
2. APIスキーマ → **インポート** → `api-site.json`
3. 初期値を入れる → **公開**
   - 値は `/Users/shun/Desktop/lindo/MICROCMS-SITE-INPUT.md` からコピー
   - `noindex` は **ON のまま**（公開日に OFF）

---

## `api-site.json` のインポートが失敗したら

このファイルは**手で組み立てている**（現サービスに `site` API がまだ無く、
エクスポートできなかったため）。キー構成は `api-artists.json` の実物に合わせてあり、
`text` / `textArea` / `repeater` は**実物と完全一致**していることを確認済み。

ただし次の2つだけは実物に参照が無く、**推定**で書いている。

| フィールド | 種類 | 状況 |
|---|---|---|
| `noindex` | 真偽値（`boolean`） | 推定 |
| `ogImage` | 画像（`media`） | 推定（複数画像 `mediaList` から推定） |

**失敗したら `api-site-minimal.json`（この2つを抜いた版）をインポートし、
2つだけ管理画面で手作りする。**

| フィールドID | 表示名 | 種類 |
|---|---|---|
| `noindex` | 検索エンジンに載せない | 真偽値 |
| `ogImage` | SNS共有画像 | 画像 |

> `noindex` は**必ず作ること**。これが無いと公開日に検索エンジンへ出す操作ができない。
> `ogImage` は空でも動くので、最悪あとまわしでよい。

---

## 検証済みのこと

- `api-site.json` の32フィールドが、`wp-theme/preview/site-data.php` が読む
  フィールドIDと**過不足なく一致**（自動照合済み）
- カスタムフィールド `service` の子（`title` / `description`）もコードと一致
- 文言のマージ挙動（上書き / 空欄は既定値 / 未送信は既定値 / `noindex` / 繰り返しの
  空行除去）をローカルで検証済み

## 関連

| | |
|---|---|
| `/Users/shun/Desktop/lindo/MICROCMS-SITE-INPUT.md` | 全フィールドの初期値（コピー元） |
| `/Users/shun/Desktop/lindo/MICROCMS-SCHEMA.md` | スキーマの意味・補足 |
| `/Users/shun/Desktop/lindo/MTG-2026-08-10.md` | 当日の進行表 |
