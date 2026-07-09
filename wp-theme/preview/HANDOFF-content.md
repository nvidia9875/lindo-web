お世話になっております。
ご返信ありがとうございます。いただいたご指定を反映しました ✨
お手すきのときにご確認いただけたら嬉しいです。


【今回の反映】

・作品一覧の見せ方を「Grid（カードを整然と並べる）」に確定しました。
・トップの見出しの動きを「Scatter（カーソルで文字が少し散らばる動き）」に確定しました。
・画面左下の切り替えパネルは、選んでいただいた形に確定しましたので撤去しました。

・No No GIRLS
　- アーティスト名：No No GIRLS
　- 作品名：GIRLS GROUP AUDITION「No No GIRLS」
　- 担当：KEY VISUAL DIRECTION / DESIGN, BRANDING DESIGN
　で正式に掲載しました。

・SugarNote
　- アー写を「ピンク背景」と「外撮影」に分けて掲載しました。
　- 「嘘だよ」MV は、いただいたサムネイル画像とあわせて「嘘だよ MV ▶」のリンクとして掲載しました。
　　（サムネイルをクリックすると YouTube が別タブで開きます）

・お写真を「大きく拡大して見る」機能（ライトボックス）を追加しました。
　作品をタップ → お写真の一覧が開き、1枚をクリックすると画面いっぱいに拡大表示されます。
　← → で前後の写真に送れ、ESC または × で閉じられます。


【ご確認をお願いしたいこと】

・SugarNote の「ピンク背景」「外撮影」の分け方が、意図に合っているかご確認ください。
・No No GIRLS の表記（大文字／小文字など）に相違があればお知らせください。


【英語表記の小さな修正（前回よりの継続確認）】

前回同様、打ち間違いと思われる箇所は公式の表記に直して掲載しています。
意図した表記でしたら元に戻しますのでお知らせください 🙏
・CANDY PANK → CANDY PINK（LE SSERAFIM）
・SAVEGE NOIR → SAVAGE NOIR（LE SSERAFIM）
・OCTOPATH → OCTPATH（フォルダ名の打ち間違いのようでしたので正式名で表記）


気になる点や直したいところがあれば、どんな小さなことでもお気軽にお知らせください 😊
よろしくお願いいたします！


---

## 開発メモ（お客様説明は不要）

### 前回の5問 → すべて解決済み
- ① No No Girls のテキスト → 支給を反映（上記）。
- ② SugarNote「嘘だよ」MV → サムネイル（`SugarNote/3/IMG_3063.JPG`）＋リンクタイルとして掲載。アー写は pink/outdoor に分割。
- ③ ライトボックス → 追加（`assets/js/lightbox.js`）。
- ④ Works の見せ方 → Grid に確定（後発の指定で Chapter から変更）。
- ⑤ Hero FX → scatter に確定。
- 左下スイッチャー → 撤去（プレビューの `render.php` / `index.html` から削除）。

### 本番WP パリティ（重要・未対応 = 将来対応）
モーダルの「作品ごとグループ表示」「MV サムネのリンクタイル」「pink/outdoor の分け掲載」は、
現状 **プレビュー（`content-manifest.php` 駆動）専用** の見せ方です。本番 WordPress の Artist CPT は
1 投稿 = フラットな 1 ギャラリーのため、ライブでは従来どおりフラット表示 ＋ ライトボックスで動作します。
ライブでも同じグループ／MV タイルを出すには、CPT に「作品グループ（タイトル＋画像群＋任意の動画URL）」を
持たせる拡張が別途必要です（今回は範囲外）。`artist-modal.php` は `works[]` があればグループ表示、無ければ
フラット表示にフォールバックする作りにしてあるため、両文脈で破綻しません。

### 画像の差し替え手順
`preview/artist-src/ホームページ用/<アーティスト>/<フォルダ>/` を置換 → `php wp-theme/preview/build-works-img.php`
→ `php wp-theme/preview/render.php > wp-theme/preview/index.html`。
（`artist-src/` は gitignore。公開は `works-img/` の WebP のみ。）
