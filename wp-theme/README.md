# LINDO WordPress テーマ（No4 Swiss Minimal）

株式会社LINDO（ビジュアルクリエイティブ）コーポレートサイト。
デザイン案 **No4「Swiss Minimal」**（硬質グリッド × 特大 Archivo タイポ × ピンク1色アクセント）を
WordPress クラシックテーマとして実装したもの。

## できること（今回の追加分）

- **アーティスト紹介**：トップ `03 Artists`（旧 Works を置換）。カードを押すと
  **モーダル**でプロフィール／タグ／SNS／**Instagram風の正方形グリッド**を表示。
  - 内容は DOM に実在 → SEO・JS無効でもクロール可。`<dialog>` でフォーカストラップ／ESC標準対応。
- **画像は WordPress で手動管理**（API不要）：アーティスト＝カスタム投稿、
  ポートレート＝アイキャッチ、グリッド＝メディアの複数選択（ドラッグ並び替え）。
- **Contact**：Contact Form 7 連携（**メール送信＋Flamingoで管理画面に蓄積**）。
  未設定時はデザイン確認用の静的フォールバックフォーム。
- **軽量アニメ**：スクロールリビール・hover演出・モーダル入退場。
  すべて `transform`/`opacity` のみ、`prefers-reduced-motion` 尊重、重いJSライブラリ無し。

## ディレクトリ

```
wp-theme/
├── lindo/                      ← これがテーマ本体（zip化して WP にアップロード）
│   ├── style.css               テーマヘッダ
│   ├── functions.php           モジュール読み込み
│   ├── front-page.php / header.php / footer.php / index.php
│   ├── inc/
│   │   ├── template.php        lindo_part() 部品ローダ
│   │   ├── setup.php           テーマサポート/メニュー/画像サイズ
│   │   ├── enqueue.php         CSS/JS 読み込み
│   │   ├── artist-cpt.php      Artist カスタム投稿タイプ
│   │   ├── artist-meta.php     メタ（情報＋グリッド画像ピッカー）
│   │   ├── artist-data.php     表示用データ整形（プレゼンテーション契約）
│   │   └── contact.php         CF7連携＋Customizer＋フォールバック
│   ├── template-parts/         WP非依存の表示部品（hero/about/service/artists/…）
│   └── assets/
│       ├── css/lindo.css       本体スタイル
│       ├── css/admin.css       管理画面メタボックス
│       └── js/ main.js / artist-modal.js / admin-gallery.js
├── preview/                    ← デザイン確認用（テーマには含めない）
│   ├── render.php              本番の template-parts を使って front-page をHTML化
│   ├── wp-shim.php             最小WP関数スタブ
│   ├── sample-data.php         サンプルArtists（picsumプレースホルダ）
│   └── index.html              生成物
├── DEPLOY-lolipop.md           ロリポップ導入手順
└── htaccess-sample.txt         セキュリティヘッダー サンプル
```

## ローカルでデザインを確認する

WordPress なしで、本番と同じ部品を使ってトップを描画できます。

```bash
cd wp-theme
php preview/render.php > preview/index.html      # HTML生成
php -S 127.0.0.1:8745 -t .                        # 簡易サーバ
# ブラウザで http://127.0.0.1:8745/preview/index.html
```

> `preview/` は確認専用。WP には `lindo/` だけを載せます。

## 本番導入

→ [DEPLOY-lolipop.md](./DEPLOY-lolipop.md)

## 設計メモ

- **container/presentational 分離**：WP固有の取得は `inc/` に閉じ込め、
  `template-parts/` には素の配列だけを渡す。だから同じ部品をプレビューでも使える（DRY）。
- アーティストは `publicly_queryable=false`（個別URLを持たず、一覧→モーダルで完結）。
- CSS の `clamp()/calc()` は `+`/`-` の前後に半角スペース必須（無いと宣言が無効化）。
- 依存プラグインは Contact Form 7 / Flamingo のみ（任意で WebP 化・reCAPTCHA 等）。
