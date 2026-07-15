# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

株式会社LINDO（K-POP/アーティスト系ビジュアルクリエイティブ、styledbylindo.com）のサイト刷新プロジェクト。No build framework — plain HTML/CSS/JS plus a WordPress classic PHP theme. Not a git repository.

## The repo holds three parallel tracks

The project evolved: it started as a multi-concept demo, the client picked **No4 "Swiss Minimal"**, and the production deliverable is now a **WordPress theme**. All three tracks still live in the tree:

1. **Demo gallery** (`index.html` + `concepts/01-08.html`) — 8 homepage concepts shown as live previews so the client could pick a direction. Legacy/review-only; `noindex`. Concepts 07 (Kinetic, GSAP/three.js) and 08 (Process, scroll-pinned) self-host their libs in `assets/vendor/`.
2. **WORKS static generator** (`works/` + `scripts/build-works.mjs`) — the original required deliverable (作品一覧→詳細, SEO/OGP/JSON-LD). **Superseded** by the Artists section in the WP theme but kept in the repo; not published in production.
3. **WordPress theme** (`wp-theme/lindo/`) — **the live production direction.** No4 Swiss Minimal. An **Artists** custom post type replaces Works as the hero section. Target host is Lolipop WordPress. This is where active work happens.

Root `assets/` (tokens.css, reveal.js, 07-kinetic.js, 08-process.js, vendor/) belongs to tracks 1–2. The WP theme has its **own separate asset tree** at `wp-theme/lindo/assets/` (lindo.css, main.js, loader.js, hero-fx.js, artist-modal.js, lightbox.js, admin-gallery.js). Editing the wrong tree is an easy mistake — confirm which track you're touching.

## Commands

```bash
# Track 1+2 — serve demo gallery + WORKS locally
python3 -m http.server 8080         # http://localhost:8080/ (gallery), /works/ (WORKS list)

# Track 2 — regenerate WORKS after editing works/works.json
node scripts/build-works.mjs        # writes works/index.html, works/<slug>/index.html, sitemap.xml

# Track 3 — preview the WP theme WITHOUT WordPress, then serve it
cd wp-theme
php preview/render.php > preview/index.html
php -S 127.0.0.1:8745 -t .          # http://127.0.0.1:8745/preview/index.html

# Track 3 — rebuild real-artist preview images after editing content-manifest.php or swapping artist-src/
php wp-theme/preview/build-works-img.php   # artist-src/ → works-img/ (WebP, longest-edge 1280px, q70). Needs cwebp (`brew install webp`)

# Lint any PHP file (do this after editing theme PHP)
php -l wp-theme/lindo/<file>.php
```

There is no test suite, linter config, or package.json. Verification is manual: regenerate/preview, then screenshot at 1440/768/375 (Playwright MCP is available) and check modal focus return.

## WP theme architecture (track 3 — the important one)

**Container / presentational split is the load-bearing pattern.** WordPress-specific data fetching is confined to `inc/`; `template-parts/` are pure views that receive only plain PHP arrays. This is what lets the *same* template parts render both in WordPress and in the standalone preview.

- `inc/` (containers): `artist-cpt.php` (Artist CPT), `artist-meta.php` (meta + wp.media gallery picker), `artist-data.php` (shapes WP posts → plain arrays like `{name, tags[], profile[], portrait{url,w,h,alt}, gallery[], links[]}`), `company.php` (representative via Customizer), `contact.php` (CF7 + fallback), `enqueue.php`, `setup.php`, `template.php`.
- `template-parts/` (presentational): never call WP data functions directly; they read variables passed in. Rendered via `lindo_part($slug, $vars)` (in `inc/template.php`) which `extract()`s `$vars` and includes the file. Parts guard with `if (!defined('LINDO_PART')) exit;`.
- `front-page.php` (WP) and `preview/render.php` (standalone) both call the **same** `front-sections` part with the same shape — the section order (Hero/About/Service/Artists/Partner/Contact) lives in `template-parts/front-sections.php` so it stays DRY. `preview/wp-shim.php` stubs the minimal WP functions.
- **Preview real-artist data is manifest-driven.** `preview/content-manifest.php` is the single source of truth (order, display names, work groupings = multiple source folders merged into one gallery, titles, roles, links, `cap`, and an optional per-work `url` that renders that work as an external video/MV **link tile** — thumbnail + ▶ — instead of a lightbox gallery). `preview/build-works-img.php` reads it and optimizes `preview/artist-src/` → `preview/works-img/` (sips auto-orients + resizes longest-edge 1280, then `cwebp` → WebP q70; capped at `cap` images/work, evenly sampled). `preview/real-data.php` then scans `works-img/` + the manifest to build the artist array `render.php` passes in. `artist-src/` is gitignored (large/private); only `works-img/` (WebP) is committed. Client-facing content decisions/typo corrections are logged in `preview/HANDOFF-content.md`. (`preview/sample-data.php` is the older sample set; `real-data.php` supersedes it.)
- **Artist CPT is `publicly_queryable=false`** — artists have no individual URLs. The UX is list (Grid of `.artist-card`) → `<dialog class="artist-modal">` (native focus trap / ESC). Content is real DOM (SEO-safe, works with JS off). `artist-modal.php` renders **per-work groups** (title + that work's full gallery) when `works[]` is present (preview, manifest-driven), else falls back to a **flat gallery** (production WP `artist-data.php`, one gallery/post). A single shared `<dialog class="lightbox">` (`assets/js/lightbox.js`, created lazily, sibling top-level — never nested) layers on top for full-screen image zoom (←/→ within the originating `.ig-grid`, ESC/backdrop close, focus restore, and it must NOT touch `html.is-locked`). **Parity caveat:** grouping / MV tiles / pink-outdoor split are preview-only richness; the live CPT is flat-gallery + lightbox until the CPT gains per-work grouping (see `preview/HANDOFF-content.md`).
- Production plugin dependencies are only **Contact Form 7 + Flamingo**. `preview/` is dev-only and is NOT shipped to WordPress — only `lindo/` is zipped and uploaded.

## Non-obvious constraints (apply to every track)

- **CSS `clamp()`/`calc()` require spaces around `+` and `-`** (e.g. `calc(var(--x) + 10px)`). Without them the whole declaration is silently invalidated. This has bitten this codebase before.
- **CSP is `script-src 'self'`** — declared in `_headers` (Netlify/Cloudflare Pages format) and `vercel.json` (Vercel). **Caveat: it is NOT actually enforced in production today.** The live host is GitHub Pages, which cannot set response headers, so both files are inert there; they only take effect on a host that reads them (Cloudflare Pages honors `_headers`). Keep all JS self-hosted regardless — no CDN scripts, including GSAP/three.js. That is what keeps the policy adoptable the moment a real host serves it, and re-adding a CDN script would silently break the CSP on the day it starts being enforced.
- **Animations use `transform`/`opacity` only**, always honor `prefers-reduced-motion`, and heavy motion (concepts 07/08, hero-fx scatter) degrades to a static layout on mobile / reduced-motion / no-WebGL.
- `concepts/` and the gallery `index.html` are `noindex`. Going live means promoting the chosen concept to the production `index` and lifting the noindex / robots disallow.

## Configuration that must change before production

- `scripts/build-works.mjs`: `SITE_URL` (default `https://styledbylindo.com`), `ORG` block (address/tel/email), and `ORG.sameAs` (SNS URLs).
- Brand tokens: dark olive/khaki `#3c3a20` × dusty rose `#e79cb0`; WP theme is sand-ground (`#eae5d7`) + Archivo (欧文) / Zen Kaku Gothic New (和文) as **placeholders** — per the client, the real logo SVG (when supplied) drives a font/typography re-selection across the whole site.
- `works/works.json` and `preview/sample-data.php` use placeholder/sample data. The **real** client work now lives in `preview/content-manifest.php` → `preview/works-img/` (9 artists: SEVENTEEN, LE SSERAFIM, TOMORROW X TOGETHER, NMB48, BMSG, 高嶺のなでしこ, OCTPATH, SugarNote, No No GIRLS). Client feedback round 1 is applied: Works = **Grid**, hero = **scatter**, No No GIRLS copy supplied, SugarNote split into pink/outdoor + a 「嘘だよ」MV link tile, lightbox added, preview comparison switcher removed. Remaining/parity items are tracked in `preview/HANDOFF-content.md`.
