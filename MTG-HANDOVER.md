# MTG当日 引き継ぎ実行手順（2026-07-23）

伊藤さんと**一緒に画面を見ながら上から実行**するための手順書。

## 今日「完全に引き継ぐ」の定義

現実的に、今日ちゃんと終わるのは **① 器（インフラ）の所有権移管** と **② パイプラインが先方アカントで動くことの証明** まで。
これが終われば「サイトの持ち主＝先方」「更新すれば自動で本番に出る」状態になり、**引き継ぎの本体は完了**。

以下は今日は終わらない or やらない（後述の理由）:
- **235枚の画像投入** … 手作業。SugarNote(19枚)を一緒にやって流れを体得してもらい、残り8組は後日でOK（入れた分から自動で本番に出る）
- **ドメイン移管 / メール(MX) / フォーム** … MXを壊すと先方のメールが止まる。会議中に急いでやる作業ではない。**方針だけ今日決める**

---

## ◆ Part 0. 会議前にサカイが済ませておく（10分・重要）

- [ ] **現サービス `lindo` のスキーマをエクスポート**しておく（会議中に手作業で作り直さないため）
      microCMS管理画面 → API「アーティスト」→ API設定 → APIスキーマ → エクスポート（JSON保存）
      ※ カスタムフィールド `work` も含まれるか確認。含まれなければ別途エクスポート
- [ ] `gh` が使えてリポジトリ権限があるか確認: `gh auth status` / `gh variable list --repo nvidia9875/lindo-web`
- [ ] SugarNoteの画像フォルダを開いておく: `/Users/shun/Desktop/lindo/wp-theme/preview/works-img/SugarNote/`
- [ ] この手順書と `/Users/shun/Desktop/lindo/HANDOVER.md` に目を通しておく

---

## ◆ Part 1. インフラ引き継ぎ（会議の本体・30〜45分）

### 順番が命。この順でやること
> 先に GitHub の鍵を差し替えてしまうと、先方CMSが空（0組）でビルドが失敗する（＝仕様。空サイトを本番に出さない安全装置）。
> なので「先方CMSに最低1組入れて公開 → それから鍵を差し替え」の順で進める。

### 1-1. 先方アカウントで microCMS を作る　【伊藤さんの操作】
- [ ] 伊藤さん（株式会社LINDO）名義で microCMS 新規登録 → サービス作成
      - プラン: **Hobby（無料・クレカ不要）**
      - サービスID: 任意（例 `lindo` が空いていなければ `lindo-web` 等）。決めたIDをメモ → `新サービスID = ____________`
- [ ] **サカイをメンバー招待**（設定 → 権限管理 → メンバー招待。3名まで無料）

### 1-2. スキーマを移す　【サカイ】
- [ ] 新サービスで API「アーティスト」（**リスト形式**・エンドポイント `artists`）を作成
- [ ] Part 0 でエクスポートしたスキーマJSONを**インポート**
- [ ] カスタムフィールド `work` が入っているか確認（無ければ手動: `title`/`role`/`gallery`(複数画像)/`url`）
      ※ 迷ったら `/Users/shun/Desktop/lindo/MICROCMS-SCHEMA.md` の表どおり

### 1-3. SugarNote を1組だけ入れて公開（動作証明用）　【一緒に】
- [ ] コンテンツ追加 → 下の「SugarNote」行のとおり3作品を入力（画像は `works-img/SugarNote/` から）
- [ ] **「公開」する**（下書きのままだとAPIが返さずビルドが落ちる）
- [ ] ※ ここで伊藤さんに入力の流れを体験してもらう＝残り8組を自分で入れられるようにする

### 1-4. GitHub を新サービスに向ける　【サカイ・ターミナル.appで】
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

### 1-5. ビルドして本番で確認　【サカイ】
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

### 1-6. 旧サービスを止める　【サカイ】
- [ ] 旧サービス `lindo`（サカイ名義）の**APIキーを失効**（新しい方に完全移行できたのを確認してから）
- [ ] ※ 旧サービス自体は、8組の投入完了まで念のため残しておいてよい

**✅ ここまでで「所有権＝先方 / 自動デプロイ動作」の引き継ぎは完了。**

---

## ◆ Part 2. 今日その場で決めること（所有権・方針）

「誰の名義か」を後回しにすると事故る。伊藤さんがいる今日決める。

| 対象 | 現在 | 決める内容 | 推奨 |
|---|---|---|---|
| microCMS | サカイ | → 先方名義 | **先方**（Part 1で実施） |
| **ドメイン** styledbylindo.com | ムームードメイン | **名義は先方か？** ← 要確認 | **先方名義であること** |
| Cloudflare | 未作成 | 誰の名義で作るか | **先方名義**（メール経路が乗るため。個人名義は離任時に全部止まる） |
| GitHubリポジトリ | `nvidia9875`(個人) | 当面サカイ保持でよいか | サカイ保持（先方は触らない） |
| お問い合わせフォーム | ダミー（未送信） | Workers待ち or 暫定Formspark | 下記で相談 |

### フォームの選択（公開前に必須）
- **本命: Cloudflare Workers**（送信無料）。ただし**ドメインをCloudflareに載せてから**（Email Routing前提）
- **暫定: Formspark**（$25買い切り・移管不要・すぐ動く）… 公開を急ぐなら
- → 今日は「どちらで行くか」だけ決める

### ⚠️ ドメイン移管の注意（今日は実行しない・方針決めだけ）
- **MX = `50 mx01.lolipop.jp`** … `contact@styledbylindo.com` はここで受信中。**移管でMXを引き継がないとメールが止まる**
- TXTに Canva / Google の認証あり → これも引き継ぐ
- 現行サイトが styledbylindo.com で生きている（ロリポップ）→ 切替タイミングを合意
- **これは会議後に落ち着いてやる。** 今日は「先方名義で行く」「いつ切り替えるか」だけ握る

---

## ◆ Part 3. 画像投入リファレンス（SugarNoteは一緒に / 残りは後日）

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

## ◆ Part 4. 会議後にサカイがやる（宿題）

- [ ] 残り8組の投入（伊藤さんと分担してもよい。入れた分から自動デプロイ）
- [ ] **お問い合わせフォーム**（Part 2で決めた方式）
- [ ] **ドメイン移管 + Cloudflare**（MX/TXTを完全に引き継ぐ・先方名義・切替タイミング合意済みで）
- [ ] Business Partner / セクション文言 を microCMS で編集可能に（オブジェクト形式API 1本）
      ※ `inc/partners.php` のCustomizer対応はWPの機能なので現構成では無効。作り直しが必要
- [ ] ロゴ支給後: 差し替え＋フォント再選定、favicon作成
- [ ] 全組投入後: 旧方式（`real-data.php`/`works-img/`/WPテーマ一式）を撤去

---

## ◆ もし会議中に詰まったら（安全策）

- **鍵を差し替えたらビルドが落ちる/サイトが1組も出ない** → 先方CMSが空 or 未公開。SugarNoteを**公開**したか確認
- **とにかく元に戻したい** → 旧サービス `lindo` のキーに戻すだけ:
  ```
  gh variable set MICROCMS_SERVICE_ID --repo nvidia9875/lindo-web --body "lindo"
  gh secret   set MICROCMS_API_KEY    --repo nvidia9875/lindo-web   # 旧キーを貼る
  gh workflow run deploy.yml --repo nvidia9875/lindo-web
  ```
  → 旧サービスのキーを失効する前なら、いつでもこれで復帰できる（Part 1-6を最後にやる理由）
- **本番は絶対に壊れない**: ビルドが失敗した場合はデプロイされず、直前の正常な状態が残る

詳細な背景は `/Users/shun/Desktop/lindo/HANDOVER.md` / `/Users/shun/Desktop/lindo/TODO.md`。
