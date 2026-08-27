# MTG当日 引き継ぎ実行手順（2026-07-26）

> ⚠️ **この文書は制作中の作業メモで、内容は古い。**
> 最新は 技術面 → `HANDOVER-DEV.md` / 先方向け運用 → `HANDOVER-LINDO.md`。
> ここは経緯の記録としてのみ残してある。

伊藤さんと**一緒に画面を見ながら上から実行**するための手順書。

表記: 【伊】= 伊藤さんの操作 / 【サ】= サカイの操作 / 【両】= 一緒にやる

---

## ★前提：名義は決定済み（2026-07-26）

**GitHubリポジトリ以外は、すべて株式会社LINDO名義で持つ。**

| 対象 | 名義 | 今日やること |
|---|---|---|
| microCMS（コンテンツ） | **LINDO** | 今日つくる（Part 1） |
| Cloudflare（DNS / 配信 / メール） | **LINDO** | 今日つくる（Part 3） |
| ドメイン styledbylindo.com（ムームー） | **LINDO** | 名義を確認（Part 4） |
| ロリポップ（現サイト＋メール） | **LINDO** | 契約状況を確認（Part 4） |
| GitHubリポジトリ | サカイ（個人） | 変更なし。※ 例外はここだけ |

これで**「サカイがいなくなってもサイトとメールは死なない」**状態になる。

### GitHubを個人保持のままにしてよい理由（2026-07-26 検討済み）

LINDO名義のOrganizationへ移す案も検討したが、**先方の負担を増やす割に得るものが無いため採用しない。**

- **リポジトリは既にPUBLIC** … コードは今この瞬間から誰でも取得できる。所有していなくてもアクセスは確保済み。別業者への引き継ぎもURLを渡すだけ
- **サイトは落ちない** … 配信をCloudflare（LINDO名義）へ移した後は、最後に生成されたHTMLが配信され続ける。サカイ離任時に止まるのは「新規公開の反映」だけで、サイトとメールは生きている
- **移管すると会議が面倒になる** … Pages のURLが変わる／Webhook URL にリポジトリ名が入る／PATをOrg所有で作り直し（Org側の承認設定で弾かれることがある）。順番の縛りが増えて事故りやすい
- 伊藤さんのGitHubアカウント作成が不要になる ＝ **今日作るアカウントは microCMS と Cloudflare の2つだけ**

**担保**: 納品時に**コード一式をzipでDriveへ**置く（Part 7）。リポジトリはPUBLICのまま維持する。

> **Cloudflareだけは譲れない。** ドメインのDNSと `contact@` のメール経路が乗るため、個人名義だと離任時に**先方のメールが止まる**。

→ **「誰の名義にするか」を議論するフェーズは終わり。今日は「作る」に集中する。**

---

## 今日のゴール（3つ）

1. **器がLINDO名義になる** … microCMS ＋ Cloudflare を伊藤さん名義で作成
2. **伊藤さんが自走できる** … 「公開」を押すだけで本番サイトが更新される（Webhook）
3. **残りの素材と方針が全部そろう** … ロゴ / 文言 / フォーム宛先 / 公開日

### 今日はやらない（理由つき）

| やらないこと | 理由 |
|---|---|
| 235枚の画像投入 | 手作業。SugarNote(3作品19枚)を一緒にやって流れを体得 → 残り8組は後日 |
| **ネームサーバーの切替** | **MXを壊すと `contact@` のメールが止まる。** 会議中に急ぐ作業ではない |
| お問い合わせフォームの実装 | 方式だけ今日決める |
| ロゴ反映・フォント再選定 | ロゴを受け取ってから |

### 時間が足りなくなったら削る順

削ってよい順: **Part 2（Webhook）→ Part 3（Cloudflare）→ Part 4（名義確認）**
- Part 2 はサカイが後日ひとりでできる（それまでは手動ビルドで凌げる）
- Part 3・Part 4 は伊藤さん同席が要るが、後日15分もらえば足りる

**絶対に落とせないのは Part 1（microCMS）と Part 5（もらう・決める）。**
Part 5 は「伊藤さんがその場にいないと進まない」ものだけを集めてある。

### タイムテーブル目安（90分）

| 時間 | 内容 |
|---|---|
| 0:00–0:05 | 上記ゴールの共有 |
| 0:05–0:35 | Part 1-1〜1-2　microCMS作成・スキーマ移送 |
| 0:35–0:50 | Part 1-3　SugarNote を一緒に入力（＝操作の体得） |
| 0:50–1:00 | Part 1-4〜1-5　鍵の差し替え・本番確認 |
| 1:00–1:10 | Part 2　Webhook（自走化） |
| 1:10–1:25 | Part 3　Cloudflare 作成 |
| 1:25–1:30 | Part 4　ドメイン／メールの名義・契約確認 |
| 1:30–1:45 | Part 5　もらう・決める |

---

## ◆ Part 0. 会議前にサカイが済ませておく（15分・重要）

- [ ] **現サービス `lindo` のスキーマをエクスポート**しておく（会議中に手作業で作り直さないため）
      microCMS管理画面 → API「アーティスト」→ API設定 → APIスキーマ → エクスポート（JSON保存）
      ※ カスタムフィールド `work` も含まれるか確認。含まれなければ別途エクスポート
- [ ] **API「サイト設定」（`site`・オブジェクト形式）を現サービスに作ってエクスポート**しておく
      会議中にフィールドを30個手打ちするのは無理。仕様は `/Users/shun/Desktop/lindo/MICROCMS-SCHEMA.md`「2.5」
      ※ ここまでやっておけば、会議では import → 初期値の貼り付けだけで済む
- [ ] `gh` が使えてリポジトリ権限があるか確認: `gh auth status` / `gh variable list --repo nvidia9875/lindo-web`
- [ ] **Fine-grained PAT を先に作っておく**（Part 2 用。会議中に作ると詰まる）
      GitHub → Settings → Developer settings → Personal access tokens → Fine-grained
      - Repository access: `nvidia9875/lindo-web` のみ
      - Permissions: **Contents: Read and write**
      - 生成した文字列は会議まで安全な場所に控える（会話ログ・チャットに貼らない）
- [ ] **伊藤さんに渡す運用ガイドを用意**: `/Users/shun/Desktop/lindo/HANDOVER-LINDO.md`
      冒頭の「サービスID」「サイトURL」は空欄なので、Part 1-1 で決まり次第その場で埋める
- [ ] SugarNoteの画像フォルダを開いておく: `/Users/shun/Desktop/lindo/wp-theme/preview/works-img/SugarNote/`
- [ ] この手順書と `/Users/shun/Desktop/lindo/HANDOVER.md` に目を通しておく
- [ ] Part 5 の「もらうもの」を伊藤さんに**事前に一言リマインド**（特にロゴの元データ）

---

## ◆ Part 1. microCMS を LINDO 名義へ（会議の本体・30〜45分）

### 順番が命。この順でやること
> 先に GitHub の鍵を差し替えてしまうと、先方CMSが空（0組）でビルドが失敗する（＝仕様。空サイトを本番に出さない安全装置）。
> なので「先方CMSに最低1組入れて公開 → それから鍵を差し替え」の順で進める。

### 1-1. LINDO名義で microCMS を作る　【伊】
- [ ] 伊藤さん（株式会社LINDO）名義で microCMS 新規登録 → サービス作成
      - プラン: **Hobby（無料・クレカ不要）**
      - サービスID: 任意（例 `lindo` が空いていなければ `lindo-web` 等）。決めたIDをメモ → `新サービスID = ____________`
- [ ] **サカイをメンバー招待**（設定 → 権限管理 → メンバー招待。3名まで無料）

### 1-2. スキーマを移す　【サ】
- [ ] 新サービスで API「アーティスト」（**リスト形式**・エンドポイント `artists`）を作成
- [ ] Part 0 でエクスポートしたスキーマJSONを**インポート**
- [ ] カスタムフィールド `work` が入っているか確認（無ければ手動: `title`/`role`/`gallery`(複数画像)/`url`）
      ※ 迷ったら `/Users/shun/Desktop/lindo/MICROCMS-SCHEMA.md` の表どおり

### 1-2b. API「サイト設定」を作る（**文言を先方が直せるようにする**）　【サ】
> これが無いと、ヒーローの一行を変えるだけでサカイへの依頼が発生する。**今日作る価値が高い。**
> 作らなくてもサイトは既定値で正常に出る（警告が出るだけ）ので、時間が無ければ後日でもよい。

- [ ] API「サイト設定」（**オブジェクト形式**・エンドポイント `site`）を作成
- [ ] Part 0 でエクスポートしたスキーマJSONを**インポート**（`service` カスタムフィールドも）
- [ ] 初期値を投入 → **公開**
      値は `/Users/shun/Desktop/lindo/wp-theme/lindo/inc/site-defaults.php` が**現在サイトに出ている文言そのもの**。ここから写す
- [ ] フィールドの意味は `/Users/shun/Desktop/lindo/MICROCMS-SCHEMA.md`「2.5 API: `site`」の表どおり

### 1-3. SugarNote を1組だけ入れて公開（動作証明用）　【両】
- [ ] コンテンツ追加 → Part 6 の「SugarNote」行のとおり3作品を入力（画像は `works-img/SugarNote/` から）
- [ ] **「公開」する**（下書きのままだとAPIが返さずビルドが落ちる）
- [ ] ※ ここで伊藤さんに入力の流れを体験してもらう＝残り8組を自分で入れられるようにする

### 1-4. GitHub を新サービスに向ける　【サ・ターミナル.appで】
> `!` で実行しないこと（キーが会話ログに残る）。ターミナル.appで直接。

- [ ] 新サービスの**取得用(GET)APIキー**を発行（権限管理 → APIキー）
- [ ] サービスIDを差し替え:
      ```
      gh variable set MICROCMS_SERVICE_ID --repo nvidia9875/lindo-web --body "新サービスID"
      ```
- [ ] APIキーを差し替え（対話で貼る。履歴に残さない）:
      ```
      gh secret set MICROCMS_API_KEY --repo nvidia9875/lindo-web
      ```

### 1-5. ビルドして本番で確認　【サ】
- [ ] 手動ビルド → 完了待ち:
      ```
      gh workflow run deploy.yml --repo nvidia9875/lindo-web
      gh run watch --repo nvidia9875/lindo-web
      ```
- [ ] 本番を開いて SugarNote が出るか確認: https://nvidia9875.github.io/lindo-web/
- [ ] 確認コマンド（任意）:
      ```
      curl -s https://nvidia9875.github.io/lindo-web/wp-theme/preview/index.html | grep -c 'images.microcms-assets.io'
      ```
      → 1以上なら先方CMSから配信できている

### 1-6. 旧サービスを止める　【サ】
- [ ] 旧サービス `lindo`（サカイ名義）の**APIキーを失効**（新しい方に完全移行できたのを確認してから）
- [ ] ※ 旧サービス自体は、8組の投入完了まで念のため残しておいてよい

**✅ ここまでで「所有権＝LINDO / 自動デプロイ動作」の引き継ぎは完了。**

---

## ◆ Part 2. Webhook で自走化（10分）

**これを入れると、伊藤さんが「公開」を押すだけで本番サイトが更新される。**
入れないと、8組を投入するたびに**サカイに手動ビルドを依頼する**必要がある。引き継ぎの実質的な仕上げなので、今日やる価値が高い。

- [ ] 【サ】Part 0 で作った Fine-grained PAT を用意
- [ ] 【サ】新サービスの `artists` API → **Webhook** → 「カスタム通知」で設定:
      - URL: `https://api.github.com/repos/nvidia9875/lindo-web/dispatches`
      - メソッド: `POST`
      - ヘッダ: `Authorization: Bearer <PAT>` / `Accept: application/vnd.github+json`
      - ボディ: `{"event_type":"microcms-update"}`
      - 通知タイミング: コンテンツの**公開・更新・削除**
- [ ] 【両】動作確認: 伊藤さんに SugarNote をどこか1文字だけ直して「公開」を押してもらう
      → `gh run watch --repo nvidia9875/lindo-web` でビルドが自動で走れば成功
- [ ] 【両】**伊藤さんに伝える**: 反映まで**1〜2分かかる**。押した直後にサイトを見ても変わっていない

> ワークフロー側の受け口（`repository_dispatch: types: [microcms-update]`）は**設置済み**。CMS側の設定だけで有効になる。

---

## ◆ Part 3. Cloudflare を LINDO 名義で作る（15分・NS切替はしない）

**今日やるのはアカウント作成とDNSレコードの取り込みまで。ネームサーバーは切り替えない。**
Cloudflareにゾーンを追加しただけでは**現在の運用に一切影響がない**（ムームーのネームサーバーを向け替えるまで何も起きない）。安全。

将来ここに乗るもの: 独自ドメイン配信 / CSPの有効化 / お問い合わせフォーム / メール転送。
**すべてLINDO名義のアカウントに乗る**ので、この土台を今日つくっておく。

### 3-1. アカウント作成　【伊】
- [ ] 伊藤さん（株式会社LINDO）名義で Cloudflare に新規登録（無料プラン）
      - 受信できるメールアドレスで登録する（ドメイン認証メールが届く）
- [ ] **サカイをメンバー招待**（Manage Account → Members → Invite / ロール: Administrator）
      ※ 無料プランで招待できない場合はここで粘らない。Part 7 に回す

### 3-2. ゾーン追加とDNS取り込み　【サ】
- [ ] 「Add a site」→ `styledbylindo.com` → 無料プラン選択
- [ ] Cloudflareが既存レコードを自動スキャンする → **下の表と1件ずつ突き合わせる**
- [ ] **足りないレコードを手で追加する**（自動スキャンは MX / TXT を取りこぼすことがある）
- [ ] A レコードは**「DNS only」（グレーの雲）**にしておく
      → 切替の瞬間に挙動が今日とまったく同じになる。オレンジ（プロキシ）にすると現行サイトの前にCloudflareが入る
- [ ] **⚠️ ネームサーバーは切り替えない。** Cloudflare上で "Pending Nameserver Update" のまま放置してよい

### 現在のDNS（2026-07-26 実測・これを完全に再現する）

| 種別 | 名前 | 値 | 用途 | 消した場合 |
|---|---|---|---|---|
| A | `@` | `103.169.142.0` | ロリポップの現行サイト | 現サイトが落ちる |
| A | `www` | `103.169.142.0` | 同上（www） | www が落ちる |
| **MX** | `@` | **`50 mx01.lolipop.jp`** | **`contact@` の受信** | **メールが止まる** |
| TXT | `@` | `google-site-verification=Ap3jrWnHAw9ufw-9gdNmFk5Uo9jIBv6CF6xaSf7AaiA` | Search Console | 認証が外れる |
| TXT | `@` | `canva-domain-verify=5dcaf083-9df4-4481-a928-722c56714e25` | Canva | 認証が外れる |
| TXT | `_dmarc` | `v=DMARC1; p=none;` | DMARC | ポリシー消失 |
| NS | `@` | `dns01.muumuu-domain.com` / `dns02.muumuu-domain.com` | 現ネームサーバー | **今日は触らない** |

> 補足: **SPF（`v=spf1`）は現在存在しない。** 移管後もメール送信を続けるなら別途検討する（今日は判断不要）。

---

## ◆ Part 4. ドメイン／メールの名義・契約を確認（5分）

名義はLINDOで決定済みなので、**「実際にそうなっているか」の確認**だけ。ここが違っていると後の移管が詰まる。

### 4-1. ムームードメイン　【伊】
- [ ] ムームードメインにログインできるか（伊藤さんのアカウントか？）→ `アカウント所有者 = ____________`
- [ ] ドメイン情報（Whois）の**登録者名が株式会社LINDO**になっているか → `登録者 = ____________`
- [ ] 違っていた場合は、後日 Whois 情報変更 or アカウント移管が必要（メモだけ取る）

### 4-2. ロリポップ　【伊】※ここが盲点
現行サイトとメールが**同じロリポップ契約に同居**している。新サイト公開後の扱いを確認する。

- [ ] ロリポップの契約者は誰か → `____________`
- [ ] **`contact@styledbylindo.com` で「送信」もしているか？（受信だけか）** → `受信のみ / 送受信`
- [ ] 他にメールアドレスを作っていないか（`info@` など） → `____________`

> **なぜ重要か**: 新サイト公開後「もうロリポップは要らない」と解約すると、**メールも同時に消える**。
> - **受信だけ**なら → Cloudflare Email Routing（無料）に移せる。ロリポップ解約可＝コスト削減
> - **送信もしている**なら → 解約すると送れなくなる。ロリポップ継続か Google Workspace 等が必要
>
> 今日は結論を出さなくてよい。**「解約したらメールも消える」ことを伊藤さんと共有する**のが目的。

---

## ◆ Part 5. その場でもらう・決める（15分）

**伊藤さんがいないと進まないものだけ。** ここを取りこぼすと後日また時間をもらうことになる。

### 5-1. もらうもの（Google Drive等で受け取る）

- [ ] **ロゴの元データ** … `.ai` / `.svg` / `.pdf`（**PNGだけだと不可**。拡大で滲む・favicon が作れない）
      → 受け取り次第、**サイト全体のフォント再選定**とセットで着手（現在の Archivo / Zen Kaku Gothic New は仮）
- [ ] **Business Partner の企業リスト** … **この一覧が正しいか確認**、増減があれば最新版
- [ ] **セクション文言の最終稿** … Hero / What We Do / About（会社概要・代表プロフィール） / Contact
- [ ] **アーティストの紹介文**（任意） … 現在9組とも**プロフィール欄が空**。入れるなら原稿がいる。不要なら「不要」と決める

> 上の2つは **1-2b の `site` API を作れば、その場で伊藤さん自身が直せる**（サカイ経由が不要になる）。
> 会議中に直す時間が無くても「あとで自分で直せる」状態にしておくのが目的。

### 5-2. 決めること

- [ ] **お問い合わせの送信先メールアドレス** → `____________`
      （`contact@styledbylindo.com` でよいか。別アドレスに飛ばしたいか）
- [ ] **フォームの方式**
      - **本命: Cloudflare Workers**（送信無料・同一オリジンで動くのでCSPに適合）
        … ただし**ドメインをCloudflareに載せてから**（Email Routing前提）＝ Part 3 の続きが要る
      - **暫定: Formspark**（$25買い切り・移管不要・すぐ動く）… 公開を急ぐなら
      - → **公開日から逆算して決める**（下記）
- [ ] **公開日（現行サイトからの切替日）** → `____________`
      これが決まると、ネームサーバー切替日・フォームを暫定にするかが自動的に決まる
- [ ] **アーティストの表示順**（`order` 1〜9）の最終確認
      現在: 1 SEVENTEEN / 2 LE SSERAFIM / 3 TOMORROW X TOGETHER / 4 NMB48 / 5 BMSG / 6 高嶺のなでしこ / 7 OCTPATH / 8 SugarNote / 9 No No GIRLS
- [ ] **残り8組の投入の分担と期限** → `伊藤さん: ______ / サカイ: ______ / 期限: ______`
      入れた分から自動で本番に出るので、分担して並行で進められる

---

## ◆ Part 6. 画像投入リファレンス（SugarNoteは一緒に / 残りは後日）

**入れ方**: コンテンツ「アーティスト」→ 追加 → 下表のとおり。
- **画像**: `/Users/shun/Desktop/lindo/wp-theme/preview/works-img/<アーティスト>/<作品フォルダ>/` の中身をそのまま複数画像にアップ（加工不要）
- **1フォルダ = 1作品**（繰り返しフィールドの1行）
- **写真の順番・作品の順番はドラッグで調整可**。`order` はアーティストの表示順（下表の番号）
- 同じ画像を2回入れると**ビルドで警告**が出る（05.webp重複が実際に起きた）ので注意

### order 1 — SEVENTEEN（担当: Style Direction）
| 作品名 | 担当 | フォルダ | 枚数 |
|---|---|---|---|
| JP 4th Single「消費期限」 | Style Direction | `SEVENTEEN/1-shohikigen` | 15 |
| JAPAN BEST ALBUM「ALWAYS YOURS」 | Style Direction | `SEVENTEEN/2-always-yours` | 15 |

### order 2 — LE SSERAFIM（担当: Style Direction）
| 作品名 | フォルダ | 枚数 |
|---|---|---|
| JP 3rd Single「CRAZY」CONCEPT PHOTO｜BLACK INSANITY | `LESSERAFIM/1-black-insanity` | 15 |
| JP 3rd Single「CRAZY」CONCEPT PHOTO｜CRAZY DUSK | `LESSERAFIM/2-crazy-dusk` | 11 |
| JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜AIRY BLOOM | `LESSERAFIM/3-airy-bloom` | 11 |
| JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜CANDY PINK | `LESSERAFIM/4-candy-pink` | 12 |
| JP 2nd Single「UNFORGIVEN」CONCEPT PHOTO｜SAVAGE NOIR | `LESSERAFIM/5-savage-noir` | 12 |
| Japan Debut Single「FEARLESS」Japanese ver. | `LESSERAFIM/6-fearless` | 15 |
| AERA（2024年12月号） | `LESSERAFIM/aera` | 2 |
（担当は全作品 Style Direction）

### order 3 — TOMORROW X TOGETHER（担当: Style Direction）
| 作品名 | 担当 | フォルダ | 枚数 |
|---|---|---|---|
| JP 2nd Album「SWEET」 | Style Direction | `TOMORROW X TOGETHER/1-sweet` | 15 |

### order 4 — NMB48（担当: Style Direction / Styling）
| 作品名 | フォルダ | 枚数 |
|---|---|---|
| これが愛なのか | `NMB48/1-korega-ai` | 15 |
| がんばらぬわい | `NMB48/2-ganbaranuwai` | 11 |
| andMIKANA（山本望叶） | `NMB48/3-andmikana` | 10 |
（担当は全作品 Style Direction / Styling）

### order 5 — BMSG（担当: Creative / Style Direction）
| 作品名 | 担当 | フォルダ | 枚数 |
|---|---|---|---|
| BMSG ARTIST｜New Year Photo | Style Direction / Prop Design | `BMSG/1-new-year-photo` | 15 |
| BMSG FES 2025 Package | Creative Direction | `BMSG/2-fes2025` | 3 |
| BMSG TRAINEE｜Digital EP「Forked Road」（RUI / TAIKI / KANON） | Jacket Direction / Design | `BMSG/3-forked-road` | 1 |

### order 6 — 高嶺のなでしこ（担当: Style Direction / Styling / Design）
| 作品名 | フォルダ | 枚数 |
|---|---|---|
| 「アイドル衣装」MV | `高嶺のなでしこ/1-idol-isho-mv` | 11 |
| 夏衣装 | `高嶺のなでしこ/2-natsu-isho` | 10 |
（担当は全作品 Style Direction / Styling / Design）

### order 7 — OCTPATH（担当: Styling / Design）※フォルダ名は OCTOPATH
| 作品名 | フォルダ | 枚数 |
|---|---|---|
| LIVE -UP TO THE SKY- | `OCTOPATH/1-up-to-the-sky` | 4 |
| ARENA LIVE -SPARKLE- | `OCTOPATH/2-sparkle` | 9 |
（担当は全作品 Styling / Design）

### order 8 — SugarNote（担当: Visual Creative）★今日一緒に入れる
| 作品名 | 担当 | フォルダ | 枚数 | URL(任意) |
|---|---|---|---|---|
| Artist Photo（ピンク背景） | Visual Creative | `SugarNote/1-artist-photo-pink` | 11 | — |
| Artist Photo（外撮影） | Visual Creative | `SugarNote/2-artist-photo-outdoor` | 7 | — |
| 「嘘だよ」MV | Creative Produce | `SugarNote/3-usodayo-mv` | 1 | `https://youtu.be/lRI7AdFnMDk` |

### order 9 — No No GIRLS（担当: KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN）
| 作品名 | フォルダ | 枚数 |
|---|---|---|
| GIRLS GROUP AUDITION「No No GIRLS」 | `NoNoGirls/main` | 4 |

> `url` を入れた作品（SugarNoteのMVなど）は、サイト上で **▶付きのリンクタイル**になり YouTube へ飛ぶ。
> `url` が空なら通常のギャラリー（クリックで拡大）。

---

## ◆ Part 7. 会議後にサカイがやる（宿題）

優先度順:

1. [ ] **お問い合わせフォーム**（Part 5-2 で決めた方式）… 公開前に必須。現状は押しても何も送信されないダミー
2. [ ] **ネームサーバー切替 + 配信の移行**（Part 3 の続き・公開日に合わせて）
      - Cloudflare のDNSレコードを再点検 → ムームーでネームサーバーを Cloudflare に向ける
      - 切替後 `MX` が生きているか必ず確認（`dig +short styledbylindo.com MX`）→ テストメールを送受信
      - **Cloudflare Pages へ配信を移す**（`_headers` が効くようになり、現状死んでいるCSPが復活する）
        ※ 配信もLINDO名義のCloudflareに乗る。GitHub側はビルド用リポジトリとして残るだけ
      - ⚠️ **Pagesプロジェクトは必ず「Direct Upload」で作ること**（GitHub Actions でビルド → wrangler で配信）
        - **CloudflareのビルドイメージにPHP 8.3が無い**（v1でPHP 5.6/7.2/7.4のみ、v2以降は非搭載）＝ Git連携させてもビルドできない
        - **Git連携で作ると後からDirect Uploadに変更できない**（Cloudflare公式）。作り方を間違えるとプロジェクト作り直し
3. [ ] 残り8組の投入（Part 5-2 の分担どおり。入れた分から自動デプロイ）
4. [ ] ロゴ支給後: 差し替え＋**フォント再選定**、favicon作成
5. [ ] **コード一式をLINDOへ納品**（zipでDriveへ）… GitHubを個人保持にする代わりの担保。リポジトリはPUBLICのまま維持する
6. [ ] Cloudflare にサカイをメンバー招待（Part 3-1 で無料プランでは不可だった場合）
7. [ ] 全組投入後: 旧方式（`real-data.php`/`content-manifest.php`/`build-works-img.php`/`works-img/`/WPテーマ一式）を撤去
      ※ `template-parts/` と `assets/` は**現役**。消さないこと

---

## ◆ もし会議中に詰まったら（安全策）

- **鍵を差し替えたらビルドが落ちる/サイトが1組も出ない** → 先方CMSが空 or 未公開。SugarNoteを**公開**したか確認
- **Webhookを設定したのにビルドが走らない** → PATの権限（Contents: Read and write）とリポジトリ指定を確認。動かなければ手動ビルドで進めて後日調整（本体の引き継ぎは止まらない）
- **Cloudflareでゾーン追加に失敗する** → 今日はスキップして構わない。**現在の運用には何の影響もない**（ネームサーバーを触っていないため）
- **とにかく元に戻したい** → 旧サービス `lindo` のキーに戻すだけ:
  ```
  gh variable set MICROCMS_SERVICE_ID --repo nvidia9875/lindo-web --body "lindo"
  gh secret   set MICROCMS_API_KEY    --repo nvidia9875/lindo-web   # 旧キーを貼る
  gh workflow run deploy.yml --repo nvidia9875/lindo-web
  ```
  → 旧サービスのキーを失効する前なら、いつでもこれで復帰できる（Part 1-6を最後にやる理由）
- **本番は絶対に壊れない**: ビルドが失敗した場合はデプロイされず、直前の正常な状態が残る

詳細な背景は `/Users/shun/Desktop/lindo/HANDOVER.md` / `/Users/shun/Desktop/lindo/TODO.md`。
