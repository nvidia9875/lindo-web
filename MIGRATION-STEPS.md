# microCMS 移管の作業手順（サカイ用）

> ⚠️ **この文書は制作中の作業メモで、内容は古い。**
> 最新は 技術面 → `HANDOVER-DEV.md` / 先方向け運用 → `HANDOVER-LINDO.md`。
> ここは経緯の記録としてのみ残してある。

サービス `lindo-web`（LINDO名義・招待済み）へ移す作業。上から順に。

最終更新: 2026-08-22

---

## 進捗

- [x] 招待を受け取る
- [x] GitHub の `MICROCMS_SERVICE_ID` を `lindo-web` に差し替え
- [ ] 以下すべて

---

## 0. APIキーを発行する ★最初

管理画面 → **権限管理 → APIキー** で発行。

**権限は GET と PATCH の両方**を付けること。

| 権限 | 何に使うか |
|---|---|
| **GET** | サイトのビルド（本番・ローカル共通） |
| **PATCH** | サイト設定32項目の一括投入（手順3の自動化） |

> 画像アップロードのAPIは Hobby プランでは使えないので、その権限は不要。

### 置き場所

```bash
# GitHub（ターミナル.app で。! 経由だと会話ログに残る）
gh secret set MICROCMS_API_KEY --repo nvidia9875/lindo-web

# ローカル（.env.local。gitignore 済み）
MICROCMS_API_KEY=<新しいキー>
MICROCMS_SERVICE_ID=lindo-web
```

> ⚠️ いま `DATA_SOURCE=local` なので、鍵を差し替えても**本番は影響を受けない**。
> 中身が空のうちに差し替えて問題ない。

---

## 1. API「アーティスト」を作る

1. **API を作成**
   - 形式: **リスト形式**
   - エンドポイント: `artists`
2. **APIスキーマ → インポート**
   - `microcms-schema/api-artists.json`
3. カスタムフィールド `work`（`title` / `role` / `gallery` / `url`）が入ったか確認
   ※ 同じJSONに含まれるので別作業は不要

> **手打ちしない。** フィールドIDを打ち間違えるとエラーは出ずに写真が0枚になる。

---

## 2. API「サイト設定」を作る

1. **API を作成**
   - 形式: **オブジェクト形式**（1件だけのAPI。リストではない）
   - エンドポイント: `site`
2. **APIスキーマ → インポート**
   - `microcms-schema/api-site.json`（32フィールド＋カスタムフィールド `service`）

> インポートが失敗したら `api-site-minimal.json` を入れて、
> `noindex`（真偽値）と `ogImage`（画像）の2つだけ手で足す。
> 詳細は `microcms-schema/README.md`。

---

## 3. サイト設定の中身を流し込む ★自動

**32項目を手打ちしない。**スクリプトで入れる。

```bash
cd /Users/shun/Desktop/lindo
set -a; . ./.env.local; set +a

php scripts/push-site-settings.php           # 中身の確認（送信しない）
php scripts/push-site-settings.php --write   # 実際に送信
```

送信後、**管理画面で「公開」を押す**（APIで入れても下書き状態のため）。

- `noindex` は **ON のまま**。公開日に OFF にする

---

## 4. アーティストを投入する ★ここが本番・手作業

**10組 / 27作品 / 332枚。** 内容は `MICROCMS-ARTISTS-INPUT.md` が正。

### 画像の場所

```
/Users/shun/Desktop/lindo/wp-theme/preview/works-img/<画像フォルダ>/
```

**1フォルダ = 1作品。**中身を全選択して一括アップロードすれば順番も正しく入る。
加工不要（1280px WebP 済み。配信時も再エンコードされない）。

### 1組あたりの手順

1. コンテンツ「アーティスト」→ **追加**
2. アーティスト名 / 担当 / 表示順 を入力
3. 「作品」の **追加** → 作品名・担当・写真（フォルダごと選択）
4. 外部リンク欄がある作品は URL を入れる（▶付きタイルになる）
5. **「公開」を押す**

### 投入順（表示順）

| # | アーティスト | 作品 | 枚数 |
|---:|---|---:|---:|
| 1 | SEVENTEEN | 2 | 97 |
| 2 | LE SSERAFIM | 7 | 90 |
| 3 | TOMORROW X TOGETHER | 1 | 28 |
| 4 | BMSG | 4 | 29 |
| 5 | NoNoGirls | 1 | 4 |
| 6 | NMB48 | 3 | 33 |
| 7 | OCTPATH | 2 | 13 |
| 8 | 福本大晴 | 2 | 5 |
| 9 | 高嶺のなでしこ | 3 | 14 |
| 10 | SugarNote | 2 | 19 |

> **1組入れるごとに「公開」**しておくと、途中で中断しても状態が分かる。

---

## 5. microCMS 配信へ切り替える

全部入って「公開」まで済んだら。

```bash
# ローカルで先に確認（microCMS から取得できるか）
cd /Users/shun/Desktop/lindo
set -a; . ./.env.local; set +a
./scripts/build-site.sh
php -S 127.0.0.1:8745 -t dist    # → http://127.0.0.1:8745/

# 問題なければ本番を切り替え
gh variable delete DATA_SOURCE --repo nvidia9875/lindo-web
gh workflow run deploy.yml --repo nvidia9875/lindo-web
```

### 確認すること

- [ ] アーティストが **10組** 出ているか
- [ ] 作品が **27件**、写真が **332枚** か
- [ ] SugarNote の「嘘だよ」MV が **▶付きタイル**になっているか
- [ ] 文言が「サイト設定」の値に置き換わっているか

> 失敗しても本番は壊れない。ビルドが落ちればデプロイされず、直前の状態が残る。
> 戻したいときは `gh variable set DATA_SOURCE --repo nvidia9875/lindo-web --body "local"`

---

## 6. Webhook を設定する（自走化）

これを入れると伊藤さんが「公開」を押すだけでサイトが更新される。

1. GitHub で **Fine-grained PAT** を作る
   （対象リポジトリのみ / Contents: Read and write）
   https://github.com/settings/personal-access-tokens
2. microCMS の `artists` API → **API設定 → Webhook** →「カスタム通知」
   - URL: `https://api.github.com/repos/nvidia9875/lindo-web/dispatches`
   - メソッド: `POST`
   - ヘッダ: `Authorization: Bearer <PAT>` / `Accept: application/vnd.github+json`
   - ボディ: `{"event_type":"microcms-update"}`
   - タイミング: 公開 / 更新 / 削除
3. **`site` API 側にも同じ Webhook**（文言を直しても反映されるように）
4. 1文字直して「公開」→ ビルドが自動で走ることを確認

---

## 7. 引き渡し

- [ ] `HANDOVER-LINDO.md` の冒頭の空欄を埋める
      - 管理画面: `https://lindo-web.microcms.io/`
      - サイト: `https://nvidia9875.github.io/lindo-web/`（ドメイン切替は後日）
- [ ] 伊藤さんに操作してもらう（1文字直して「公開」→ 反映を確認）
- [ ] コード一式を zip で納品
- [ ] 旧サービス `lindo` の APIキーを失効
      ※ サービス自体は切り戻し用にしばらく残す

---

## 引き渡し後（ドメイン切替のタイミング）

`STATUS.md` の「B. 引き渡し後」を参照。
