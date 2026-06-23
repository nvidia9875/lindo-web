# LINDO ウェブサイト刷新 — デザイン案ギャラリー ＋ WORKS

株式会社LINDO（Creative / Branding / Design / Styling）のサイト刷新プロジェクト。
1つのサイトの中に **複数のホームページ案** を実装し、トップの **ギャラリー** から
ライブプレビューで見比べられます。あわせて、必須要件の **WORKS（作品一覧 → 作品詳細）** を
SEO対応の静的ページとして構築しています。

ビルドフレームワークは不要（素のHTML/CSS/JS）。WORKSのみ、SEOのため
依存ゼロのNodeスクリプトで静的生成します。

---

## 見方（デザイン案）

ローカルで開くだけ、またはサーバ起動：

```bash
cd /Users/shun/Desktop/lindo
python3 -m http.server 8080
# → http://localhost:8080/        … 案ギャラリー（トップ）
# → http://localhost:8080/works/  … WORKS 一覧
```

- トップ `index.html` が **デザイン案ギャラリー**。各カードはライブ縮小プレビュー。クリックで全画面表示。
- 気に入った番号（例：02、05 …）を教えてください。その方向で全体を仕上げます／2〜3案を掛け合わせます。

### 8案
| No | 名前 | 方向性 |
|----|------|--------|
| 01 | Dark Atelier | ダーク高級・現行進化（フルブリード写真） |
| 02 | Light Editorial | ライト洗練・余白・特大タイポ（参考①寄り） |
| 03 | Cinematic | 映像的・全画面・スクロール演出（エンタメ向き） |
| 04 | Swiss Minimal | グリッド・特大タイポ・ピンク1色 |
| 05 | Bento | タイル構成・1画面で一望 |
| 06 | Gallery | 写真主役・作品が主役 |
| 07 | Kinetic | アニメーション全振り（GSAP＋Lenis＋three.js WebGL・参考dashの雰囲気） |
| 08 | Process | 画面固定スクロールテリング。制作プロセスの物語が進む（GSAP pin/scrub＋three.js・参考elvalabsの雰囲気） |

> **07 Kinetic のライブラリ**：GSAP / ScrollTrigger / SplitText / Lenis / three.js は
> `assets/vendor/` に **self-host**（CDN不使用＝CSP `script-src 'self'` のまま）。モーション制御は
> `assets/js/07-kinetic.js`。**SP・reduced-motion・WebGL非対応では自動で静的レイアウトに縮退**します。
> ギャラリーの07カードは負荷回避のため静的ポスター画像（`assets/poster-07.jpg`）を使用。

> **08 Process**：画面固定（ScrollTrigger pin）＋スクロール量で物語を scrub するスクロールテリング。
> 制御は `assets/js/08-process.js`、中央フレームの画像は `assets/process/00〜05.jpg`（same-origin・WebGL安全）。
> **デスクトップ＝固定ステージ／SP・タブレット・reduced-motion＝章を縦積みの素直版に自動縮退**。
> 全章コピーは実DOMテキスト（SEO安全）、ギャラリー08カードは静的ポスター（`assets/poster-08.jpg`）。

---

## 作品（WORKS）の追加・更新方法 ★御社運用

作品は **1か所のデータ（`works/works.json`）** を編集し、画像を置き、**生成コマンド** を実行するだけ。
作品ごとにHTMLを手書きする必要はありません。

### 手順
1. 画像を `works/images/` に置く（WebP/AVIF推奨・横1600px目安）。
2. `works/works.json` の `works` 配列に1件追記：

   ```json
   {
     "slug": "artistx-album",
     "title": "ARTIST X — Album Visual",
     "client": "Label名",
     "categories": ["Visual Creative", "Styling"],
     "year": "2025",
     "role": "Visual Direction / Styling",
     "cover": "images/artistx-cover.webp",
     "gallery": ["images/artistx-1.webp", "images/artistx-2.webp"],
     "credits": [{ "role": "Creative Direction", "name": "LINDO" }],
     "description": "一覧・SNS・検索結果に出る短い説明文。",
     "body": "詳細ページ本文（任意・改行で段落）。"
   }
   ```

3. 生成コマンドを実行：

   ```bash
   node scripts/build-works.mjs
   ```

   → `works/index.html`（一覧）・`works/<slug>/index.html`（詳細）・`sitemap.xml` が更新されます。

> **ポイント**：`slug` は半角英数・ハイフンのみ（URLになります）。重複不可。
> `categories` は一覧フィルタに使われます（例：`Visual Creative` / `Styling` / `Design Direction`）。

### 将来：管理画面で運用したい場合（microCMS等）
`works.json` と同じ項目構成のままヘッドレスCMSへ移行できます。
`scripts/build-works.mjs` の入力をローカルJSONからCMSのAPI取得に差し替え、
CMS更新時に生成を再実行（デプロイフック）する構成にできます。

---

## SEO / パフォーマンス / セキュリティ
- 各ページに固有の `title` / `meta description` / `canonical` / OGP・Twitterカード / 構造化データ（JSON-LD）。
- `sitemap.xml`・`robots.txt` を同梱。
- **レビュー中はギャラリーと `concepts/` を `noindex`**（重複ホームをインデックスさせない）。
- 画像は寸法明示・遅延読み込み、合成可能プロパティ中心のアニメ、`prefers-reduced-motion` 尊重。
- セキュリティヘッダ／CSPは `_headers`（Netlify/Cloudflare）または `vercel.json`（Vercel）。

## 公開時にやること
1. 採用案（例 `concepts/02-light-editorial.html`）を本番 `index.html` として配置。
2. ギャラリー／`concepts/` の `noindex` と `robots.txt` の `Disallow: /concepts/` を見直し。
3. 実ロゴSVG・正式フォント・本番作品画像に差し替え。
4. ドメイン確定後、`scripts/build-works.mjs` 内の `SITE_URL` を本番URLに合わせて再生成。
5. Google Search Console に `sitemap.xml` を送信。

## 未確定事項（本番前に確定）
- 本番ドメイン（canonical/OG用。既定 `https://styledbylindo.com/`）
- 実ロゴSVG・正式フォント・作品画像の支給
- SNS（Instagram等）URL（JSON-LD `sameAs`・フッター・OGP）
- お問い合わせ：mailto/tel のみ か フォーム導入か
- EN対応の要否
