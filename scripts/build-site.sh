#!/usr/bin/env bash
#
# 公開サイトを dist/ に組み立てる。CI とローカルで同じものを使う。
#
# 【なぜ dist/ に集めるのか】
# 以前は GitHub Pages にリポジトリ全体（path: .）を配信していたため、
# MTG-HANDOVER.md / TODO.md / CLAUDE.md といった内部ドキュメントや、
# 旧デモ（concepts/ works/ gallery.html）まで公開されていた。
# DNSレコード表や各種検証トークンが載っているので、独自ドメインに載せる前に
# 「配信するものを明示的に選ぶ」方式へ変える。ここに書いていないものは出ない。
#
# 使い方:
#   set -a; . ./.env.local; set +a     # MICROCMS_API_KEY を読む
#   ./scripts/build-site.sh
#   php -S 127.0.0.1:8745 -t dist      # → http://127.0.0.1:8745/
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST="$ROOT/dist"

rm -rf "$DIST"
mkdir -p "$DIST"

# ── 1. HTML を生成 ────────────────────────────────
# render.php はデータ取得に失敗すると例外を投げる（空ページを作らない）。
# set -e でここで止まるので、壊れた dist/ が配信されることはない。
( cd "$ROOT/wp-theme" && php preview/render.php ) > "$DIST/index.html"

# ── 2. テーマのアセット ───────────────────────────
# admin.css / admin-gallery.js は WordPress 管理画面専用。公開サイトでは使わない。
mkdir -p "$DIST/assets/css" "$DIST/assets/js"
cp "$ROOT/wp-theme/lindo/assets/css/lindo.css" "$DIST/assets/css/"
for js in loader hero-fx main artist-modal lightbox; do
	cp "$ROOT/wp-theme/lindo/assets/js/$js.js" "$DIST/assets/js/"
done

# ── 3. ルートに置くもの ───────────────────────────
cp "$ROOT/404.html" "$ROOT/thanks.html" "$ROOT/robots.txt" "$DIST/"

# sitemap.xml は生成する。1ページ構成なのでトップ1件だけ。
# URL は生成済みHTMLの canonical から取る（site-defaults.php の siteUrl が
# 唯一の出どころになり、ここと canonical がズレない）。
CANONICAL=$(grep -o '<link rel="canonical" href="[^"]*"' "$DIST/index.html" | head -1 | sed 's/.*href="//; s/"$//')
if [ -n "$CANONICAL" ]; then
	cat > "$DIST/sitemap.xml" <<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>${CANONICAL}</loc>
  </url>
</urlset>
XML
	echo "sitemap.xml: ${CANONICAL}"
else
	echo "::warning::canonical が無いので sitemap.xml を作らない（site-defaults.php の siteUrl を確認）"
fi

# _headers は Cloudflare Pages / Netlify 形式。GitHub Pages では効かないが、
# 配信先を移した瞬間に有効になるよう同梱しておく。
cp "$ROOT/_headers" "$DIST/"

# Jekyll の処理を止める（_ で始まるファイルが消えるのを防ぐ）。
touch "$DIST/.nojekyll"

# 独自ドメイン用。GitHub Pages は artifact 内の CNAME を読む。
# 切替前は存在しないので、あれば入れる。
[ -f "$ROOT/CNAME" ] && cp "$ROOT/CNAME" "$DIST/"

# favicon はロゴ支給前の暫定。存在すれば入れる。
[ -f "$ROOT/favicon.svg" ] && cp "$ROOT/favicon.svg" "$DIST/"
[ -f "$ROOT/favicon.ico" ] && cp "$ROOT/favicon.ico" "$DIST/"

# ── 4. 旧方式（ローカル画像）で生成した場合のみ画像を同梱 ──
# microCMS 経由なら画像は images.microcms-assets.io から配信されるので不要。
# Secret を外して退避モードで動かしたときだけ works-img/ が要る。
if grep -q 'works-img/' "$DIST/index.html"; then
	echo "データ源: ローカル works-img/（退避モード）→ 画像を同梱する"
	mkdir -p "$DIST/wp-theme/preview"
	cp -R "$ROOT/wp-theme/preview/works-img" "$DIST/wp-theme/preview/works-img"
fi

# ── 5. 検査 ───────────────────────────────────────
# PHPの警告が doctype より前に混ざるとブラウザが互換モードに落ちる。
head -c 15 "$DIST/index.html" | grep -q '<!doctype html>' \
	|| { echo "::error::index.html が doctype で始まっていない（PHPの警告混入の疑い）"; head -c 300 "$DIST/index.html"; exit 1; }

COUNT=$(grep -c 'data-modal-target' "$DIST/index.html" || true)
echo "アーティスト: ${COUNT}組"
[ "$COUNT" -ge 1 ] || { echo "::error::アーティストが0組。データ取得に失敗している"; exit 1; }

# 内部ドキュメントが紛れ込んでいないか（配信物の絞り込みが壊れたら気づけるように）。
if find "$DIST" -name '*.md' | grep -q .; then
	echo "::error::dist/ に .md が入っている。内部ドキュメントが公開される"
	find "$DIST" -name '*.md'
	exit 1
fi

echo "--- dist/ ---"
( cd "$DIST" && find . -type f | sort )
echo "合計: $(du -sh "$DIST" | cut -f1)"
