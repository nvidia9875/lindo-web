# 技術引き継ぎ書（styledbylindo.com）

**この1枚に、サイトを維持・改修するのに必要なことを全部まとめてある。**
他の `.md`（GO-LIVE.md / STATUS.md / CUTOVER.md / MTG-*.md / HANDOVER.md / TODO.md）は
制作中の作業メモで、**内容は古い**。判断に使わないこと。

最終更新: 2026-08-28（引き渡し時点）

---

## 1. 一言でいうと

**microCMS（本文・写真） × GitHub Actions（10分ごとにビルド） × GitHub Pages（配信）** の静的サイト。
WordPress は使っていない。サーバーもデータベースも無い。

```
  microCMS ──(REST API)──▶ render.php ──▶ dist/ ──▶ GitHub Pages ──▶ styledbylindo.com
   本文・写真              PHPで組み立て     配信物        無料              独自ドメイン
```

- 先方（伊藤さん）が触るのは microCMS だけ。**デザイン崩れは起こらない構造**（後述の container/presentational 分離）。
- 「公開」を押すと **最大10分**でサイトに反映される（cron ビルド）。

---

## 2. 持ちものと名義

| | 名義 | 用途 | 止まると |
|---|---|---|---|
| **microCMS** `lindo-web` | **LINDO** | 本文・写真 | 更新できなくなる（サイトは残る） |
| **GitHub** `nvidia9875/lindo-web` | **制作側（サカイ）** | ソース・ビルド・配信 | **サイトが消える** |
| **ムームードメイン** styledbylindo.com | `ms.takeda01@gmail.com` | ドメイン | サイトもメールも死ぬ |
| **ロリポップ** | 同上 | **メールのみ**（`m.ito@` / `contact@`） | メールが死ぬ |
| **Web3Forms** | LINDO | お問い合わせフォームの中継 | フォームが届かなくなる |

> 🔴 **ロリポップを解約しない。**「独自ドメイン設定」も消さない。
> サイトは GitHub に移ったが、**メールはロリポップに残っている**。
> `m.ito@styledbylindo.com` は受信だけでなく**送信**にも使われている。

> 🔴 **ドメインの自動更新が「未設定」**（期限 2027-07-30）。
> 切れるとサイトもメールも同時に止まる。ムームードメインで有効化しておくこと。

---

## 3. ソースの構成

```
lindo-web/
├── wp-theme/
│   ├── lindo/                    ← 「型」。デザインとテンプレート
│   │   ├── assets/css/lindo.css      唯一のスタイルシート
│   │   ├── assets/js/                loader / hero-fx / main / artist-modal / lightbox
│   │   ├── inc/                      WordPress用（★現在は未使用。4章参照）
│   │   └── template-parts/           ★実際に描画されるビュー
│   └── preview/                  ← 「中身」を取ってきて型に流し込む側
│       ├── render.php                エントリポイント。HTML を1枚吐く
│       ├── microcms-client.php       API通信
│       ├── microcms-data.php         microCMS → 描画用配列（アーティスト）
│       ├── site-data.php             microCMS → 描画用配列（サイト設定）
│       ├── wp-shim.php               WordPress関数の最小スタブ
│       └── real-data.php ほか         ★旧方式。退避用（4章）
├── scripts/build-site.sh         ← 配信物 dist/ を組み立てる。CIもローカルもこれ
├── .github/workflows/deploy.yml  ← ビルドと配信
├── ogp.png / favicon.svg / 404.html / thanks.html / robots.txt / CNAME / _headers
└── HANDOVER-LINDO.md             ← 先方向けの運用ガイド（これは最新）
```

### 一番大事な設計

**データ取得（container）と描画（presentational）を分けてある。**

- `template-parts/` は **WordPress の関数を一切呼ばない**。渡された素の PHP 配列だけを読む。
- そのおかげで、データ源を WordPress → microCMS に差し替えたとき、
  **変えたのは取得側の約100行だけ**で、テンプレートと CSS は無改修で済んだ。
- 次にデータ源を変えるときも同じ。`template-parts/` は触らないこと。

配列の形（契約）は `MICROCMS-SCHEMA.md`「1. 守るべき契約」にある。**ここを崩すと全部壊れる。**

---

## 4. データ源は環境変数で切り替わる

`render.php` は起動時に環境を見て、どこから中身を取るか決める。

| 環境 | 取得元 | 用途 |
|---|---|---|
| `MICROCMS_API_KEY` あり | **microCMS** | **本番。通常はこれ** |
| `MICROCMS_FIXTURE=<path.json>` | ローカルJSON | 異常系の再現（画像重複の警告など、正常データでは黙っているもの） |
| どちらも無し | `wp-theme/preview/works-img/` を走査 | **退避モード**。CMSが落ちても出せる |

CI では Variable `DATA_SOURCE=local` にすると、鍵を消さずに退避モードへ倒せる。

> **退避モードは旧方式で、画像の追加に macOS の `sips` が要る。**
> 「先方が自分で写真を足せない」のが元々の問題だったので、常用しないこと。

### WordPress テーマ（`wp-theme/lindo/inc/`）について

**動いていない。** WordPress では一度も稼働していない。残してあるのは
`template-parts/` が同居しているためだけ。**WordPress へ戻す提案はしないこと**（理由は `CLAUDE.md`）。

---

## 5. ビルドと配信

### 配信物は `dist/` に明示的に集める

`scripts/build-site.sh` に**書いてあるものだけ**が公開される。

> 以前は リポジトリ全体を配信していて、内部ドキュメント（DNSレコード表を含む）が
> 公開されていた。同じ事故を防ぐため、末尾に「`dist/` に `.md` があったらビルドを失敗させる」
> 検査を入れてある。**この検査を外さないこと。**

ほかに、ビルドを止める検査が2つある。どれも「壊れたサイトを本番に出さない」ためのもの。

- `index.html` が `<!doctype html>` で始まらない → PHP の警告が混入している
- アーティストが0組 → データ取得に失敗している

### いつビルドされるか（`.github/workflows/deploy.yml`）

| きっかけ | |
|---|---|
| `main` への push | コード変更時 |
| **`schedule` 10分ごと** | **これが実質の自動反映。microCMS の更新はこれで載る** |
| `workflow_dispatch` | 手動実行 |
| `repository_dispatch` | microCMS Webhook 用の口。**未使用**（下記） |

> **Webhook が使えなかった経緯**: microCMS のカスタム通知は `X-` で始まるヘッダーしか
> 送れず、GitHub の dispatch API に必要な `Authorization: Bearer` を付けられない。
> 専用の「GitHub Actions」通知型に Fine-grained PAT を登録したが、トークン側に
> リポジトリ権限が保存されない事象（`This token does not have access to any repositories`）が
> 解消できなかった。**代わりに10分 cron にしてある。**
> PAT が通るようになれば即時反映にでき、そのとき `schedule` は消してよい。

### CI に登録してある値

| 種別 | 名前 | 中身 |
|---|---|---|
| Secret | `MICROCMS_API_KEY` | microCMS の取得用APIキー。**秘密** |
| Variable | `MICROCMS_SERVICE_ID` | `lindo-web` |
| Variable | `WEB3FORMS_ACCESS_KEY` | フォームの中継キー。**生成HTMLに出るので秘密ではない** |
| Variable | `DATA_SOURCE` | 未設定＝microCMS / `local`＝退避モード |

`WEB3FORMS_ACCESS_KEY` が未設定だと、フォームの代わりに**メール導線**が出る。
「押しても届かないフォーム」にはならない作りにしてある。

---

## 6. ローカルで動かす

```bash
# 1. APIキーを置く（.gitignore 済。絶対にコミットしない）
printf 'MICROCMS_API_KEY=<キー>\nMICROCMS_SERVICE_ID=lindo-web\n' > .env.local

# 2. ビルドして見る
set -a; . ./.env.local; set +a
./scripts/build-site.sh
php -S 127.0.0.1:8745 -t dist        # → http://127.0.0.1:8745/

# PHP を触ったら必ず
php -l wp-theme/preview/render.php
```

テストは無い。確認は手動：1440 / 768 / 375 で見て、モーダルを開閉して
フォーカスが元のカードに戻ることを確かめる。

---

## 7. 踏みやすい地雷

- **CSS の `clamp()` / `calc()` は `+` `-` の前後に空白が要る**。無いと宣言ごと無言で無効化される。
  この案件で実際に踏んでいる。
- **JS は全部自前ホスト**。CDN を足さないこと。`_headers` の CSP（`script-src 'self'`）が前提。
  GitHub Pages はヘッダーを返せないので現状 CSP は効いていないが、配信先を移した瞬間に効く。
- **アニメーションは `transform` / `opacity` だけ**。`prefers-reduced-motion` を必ず見る。
- **`mix-blend-mode: difference` は中間色の上で消える**。Works の連番で実際に読めなくなった。
- **microCMS の未設定フィールドはキーごと返ってこない**（空文字ではない）。必ず `isset` で見る。
- **画像URLはパーセントエンコードされている**。ファイル名比較の前に `rawurldecode` すること
  （しないと名前に空白を含む作品だけ重複アップロードされる。実際に37件出た）。
- **microCMS の `gallery` はURL文字列の配列**。`[{url: ...}]` を送ると
  `'works' has unexpected data type.` で弾かれる。
- **画像変換は `fit=max` を付ける**。`?w=1280` だけだと縦長画像が**拡大**される
  （実測 853x1280 → 1280x1921、28KB→88KB）。長辺1280以下のWebPは変換せず素通しする。

---

## 8. 将来 GitHub を LINDO 名義へ移すとき

今はサカイ個人アカウントのまま。リポジトリは **public** なので中身はいつでも取得できるが、
配信そのものはこのアカウントに依存している。移すなら手順は以下。

1. LINDO 側で GitHub アカウント（または Organization）を作る
2. `Settings → General → Danger Zone → Transfer ownership` で移管
   （Actions の Secret / Variable は**引き継がれない**）
3. 移管先で Secret / Variable を再登録（5章の表）
4. `Settings → Pages` で `Source: GitHub Actions`、`Custom domain: styledbylindo.com`、
   `Enforce HTTPS` を設定
5. **DNS を1件だけ直す**（ムームーDNS 設定2）
   - `www` の CNAME を `nvidia9875.github.io` → `<新アカウント>.github.io` へ
   - **apex の A レコード4件（`185.199.108-111.153`）は変更不要**。全アカウント共通
6. 🔴 **MX / TXT / NS は絶対に触らない**（メールと各種認証が死ぬ）

証明書の再発行で数分〜十数分 HTTPS が不安定になる。メンテ時間帯にやること。

---

## 9. 残っている課題

| | 内容 |
|---|---|
| ⬜ | **検索エンジン**: microCMS「サイト設定」の `noindex` が **ON のまま**。公開日に OFF にする |
| ⬜ | **SPF レコードが無い**（TXT は Google/Canva の認証2件のみ）。メールの到達率のために追加推奨 |
| ⬜ | **ドメイン自動更新が未設定**（期限 2027-07-30） |
| ⬜ | **名義移動**: ムームー／ロリポップが `ms.takeda01@gmail.com`。社長アカウントへ移す予定 |
| ⬜ | **アーティスト紹介文が全10組空**。空でもレイアウトは成立する（`:has()` で中央寄せ） |
| ⬜ | **microCMS に `links` フィールドが無い**。`microcms-data.php` は常に空配列を返す。SNSリンクを出すならスキーマ追加が要る |
| ⬜ | **メディアの重複37件**が microCMS に残っている。サイトからは参照されていない。消すには APIキーに「メディアの削除」権限が要る |
| ⬜ | **`m.ito@` の受信メールが消える件**。Gmail の POP 取得が「サーバーにコピーを残す」になっていない可能性 |
| ⬜ | **旧方式の撤去**: `real-data.php` / `content-manifest.php` / `build-works-img.php` / `works-img/` / `wp-theme/lindo/inc/`。ただし **`template-parts/` と `assets/` は消さないこと**（現行サイトの描画本体） |

---

## 10. 触ってはいけないもの（まとめ）

- ロリポップの契約と「独自ドメイン設定」
- DNS の **MX / TXT / NS**
- `build-site.sh` 末尾の3つの検査
- `template-parts/` と `wp-theme/lindo/assets/`
