# 引き継ぎ手順（microCMS を先方アカウントで作り直す）

現在の microCMS はこちらのアカウント（サービスID `lindo`）にある。**先方アカウントで作り直して差し替える**ための手順。

コード側はサービスIDもキーもハードコードしていないので、**設定2つを差し替えるだけ**で移行できる。

---

## 全体像

```
microCMS（先方アカウント）
   │  伊藤さんが「公開」を押す
   ↓  Webhook（任意・下記4）
GitHub Actions  … .github/workflows/deploy.yml
   │  php preview/render.php を実行してHTMLを生成
   ↓
GitHub Pages    … https://nvidia9875.github.io/lindo-web/
```

- **サイトには「microCMS に入っているものだけ」が出る。**
- HTMLは**CIが生成する**。`preview/index.html` を手でコミットする運用はもう無い。

---

## 1. 先方アカウントで microCMS サービスを作る

1. 先方（伊藤さん / 株式会社LINDO）のアカウントで新規サービスを作成
   - プランは **Hobby（無料）** で足りる（3名まで / ストレージ無制限 / 転送20GB月）
   - サービスIDは任意。以後 `<新サービスID>` と呼ぶ
2. こちらのアカウントはメンバーとして招待してもらうと保守しやすい（任意）

## 2. スキーマを移す

**手作業でフィールドを作り直さないこと。** microCMS の APIスキーマ画面に**エクスポート / インポート**がある。

1. 現サービス（`lindo`）で `artists` の APIスキーマを**エクスポート**（JSON）
2. 新サービスで API `artists`（**リスト形式**）を作り、そのJSONを**インポート**
3. カスタムフィールド `work` も同様に移す

> スキーマの定義そのものは `MICROCMS-SCHEMA.md` に表で残してある。インポートが使えない場合はそれを見て手で作る。

## 3. コード側の設定を差し替える（これだけ）

GitHub リポジトリ `nvidia9875/lindo-web` の設定を2つ変える。**コードの変更は不要。**

| 種類 | 名前 | 値 | 場所 |
|---|---|---|---|
| Variable | `MICROCMS_SERVICE_ID` | `<新サービスID>` | Settings → Secrets and variables → Actions → Variables |
| Secret | `MICROCMS_API_KEY` | 新サービスの**取得用（GET）**APIキー | 同 → Secrets |

```bash
gh variable set MICROCMS_SERVICE_ID --repo nvidia9875/lindo-web --body "<新サービスID>"
gh secret   set MICROCMS_API_KEY    --repo nvidia9875/lindo-web   # 対話で貼る（履歴に残さない）
```

差し替えたら手動でビルドして確認する。

```bash
gh workflow run deploy.yml --repo nvidia9875/lindo-web
gh run watch --repo nvidia9875/lindo-web
```

> **旧サービスのAPIキーは失効させること。**

## 4. 自動反映（Webhook）※任意・未設定

これを入れると、伊藤さんが「公開」を押すだけでサイトが更新される。未設定なら手動ビルドが必要。

1. GitHub で **Fine-grained PAT** を作る（対象リポジトリのみ / `Contents: Read and write` 相当）
2. microCMS の `artists` API → **Webhook** → 「カスタム通知」で以下を設定
   - URL: `https://api.github.com/repos/nvidia9875/lindo-web/dispatches`
   - メソッド: `POST`
   - ヘッダ: `Authorization: Bearer <PAT>` / `Accept: application/vnd.github+json`
   - ボディ: `{"event_type":"microcms-update"}`
3. ワークフローは `repository_dispatch: types: [microcms-update]` で受ける（**設定済み**）

## 5. ローカルで動かす

```bash
# APIキーを置く（.gitignore 済。絶対にコミットしない）
printf 'MICROCMS_API_KEY=<キー>\n' > .env.local

cd wp-theme
set -a; . ../.env.local; set +a
php preview/render.php > preview/index.html
php -S 127.0.0.1:8745 -t .    # → http://127.0.0.1:8745/preview/index.html
```

環境変数の効き方:

| 変数 | 効果 |
|---|---|
| `MICROCMS_API_KEY` | microCMS から取得（本番と同じ） |
| `MICROCMS_SERVICE_ID` | サービスID（既定 `lindo`） |
| `MICROCMS_FIXTURE` | **開発用**。APIを叩かずローカルJSONで動かす（異常系の確認に使う） |
| どれも無し | リポジトリ内 `works-img/` を走査（**旧方式**。退避用に残してある） |

---

## 運用上の注意

### 画像は「そのまま」上げてよい

加工不要。長辺1280を超える画像は**配信時にmicroCMS側で自動縮小**される（`fit=max`）。
以前は `build-works-img.php`（macOS の `sips` 依存）でこちらが変換しており、**伊藤さんが自分で画像を追加できなかった**。それが解消されている。

- 既に長辺1280以下のWebPは**変換せず素通し**する（再変換すると逆に太るため。実測 28KB→49KB）
- ⚠️ `?w=1280` を単体で付けてはいけない。**縦長画像が拡大される**（実測 853x1280 → 1280x1921 / 28KB→88KB）。`fit=max` が必須

### 画像の重複は自動で検知される

同じ画像を2回入れるとビルドログに警告が出る（実際に発生した事故）。重複は自動で1枚に落とすが、**管理画面側も直すこと**。

### 並び順

- **作品の順番・写真の順番** … 管理画面でドラッグ
- **アーティストの順番** … `order`（数字）の昇順。小さいほど先

### ビルドが落ちる条件（意図的）

空のサイトが本番に出る事故を防ぐため、以下は**デプロイせず失敗**させる。

- microCMS への接続失敗 / APIキー不正
- アーティスト0件（**下書きのまま公開していない**場合が最有力）
- 生成HTMLが `<!doctype html>` で始まらない（PHPの警告混入 → ブラウザが互換モードに落ちる）

---

## まだ残っている課題

- **お問い合わせフォーム** … 静的サイトなので CF7 は使えない。現状のフォームは**押しても何も送信されないダミー**。公開前に代替（Formspark 等）が必須
- **Business Partner / セクションの文言** … まだPHP直書きで、管理画面から編集できない。microCMS の**オブジェクト形式API**を1本足せば対応可能
- **Cloudflare Pages への移行** … GitHub Pages はレスポンスヘッダを設定できず、`_headers` のCSPが**現状効いていない**。移せば有効化される
- **旧方式の撤去** … 全アーティストの投入後、`real-data.php` / `content-manifest.php` / `build-works-img.php` / `works-img/` と、使わなくなったWordPressテーマ一式を削除できる

詳細は `TODO.md` / `MICROCMS-SCHEMA.md`。
