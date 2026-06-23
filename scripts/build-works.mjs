#!/usr/bin/env node
/* =========================================================
   LINDO — WORKS 静的ジェネレーター（依存ゼロ / Node ESM）

   works/works.json を読み込み、以下を生成：
     - works/index.html            … 一覧（全カードを静的描画＝クロール可）
     - works/<slug>/index.html      … 作品詳細（固有 title/meta/OGP/JSON-LD）
     - sitemap.xml                  … home / works一覧 / 各作品

   実行：  node scripts/build-works.mjs
   ========================================================= */

import { readFile, writeFile, mkdir } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { dirname, resolve, join } from "node:path";

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = resolve(__dirname, "..");

/* ---- 設定（本番ドメイン確定時にここを変更） ---- */
const SITE_URL = "https://styledbylindo.com";
const SITE_NAME = "LINDO";
const ORG = {
  name: "株式会社LINDO",
  alt: "LINDO Co., Ltd.",
  tel: "+81-3-5308-5822",
  email: "contact@styledbylindo.com",
  address: {
    postalCode: "151-0066",
    region: "東京都",
    locality: "渋谷区",
    street: "西原2-34-9",
  },
  sameAs: [], // 例：["https://www.instagram.com/..."]（判明したら追加）
};

/* ---- ユーティリティ ---- */
const esc = (s = "") =>
  String(s)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");

const isAbs = (u = "") => /^https?:\/\//i.test(u);
// 画像URLを絶対化（ローカルは works/ 基準）
const absImg = (u = "") => (isAbs(u) ? u : `${SITE_URL}/works/${u}`);
// picsum 等の `/W/H` から寸法を推定
const dims = (u = "") => {
  const m = String(u).match(/\/(\d{2,5})\/(\d{2,5})(?:[/?#]|$)/);
  return m ? { w: +m[1], h: +m[2] } : null;
};
const slugRe = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

function headCommon({ title, desc, canonical, ogImage, ogType = "website", noindex = false, jsonld = [] }) {
  const ld = jsonld
    .map((o) => `<script type="application/ld+json">${JSON.stringify(o)}</script>`)
    .join("\n");
  return `<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>${esc(title)}</title>
<meta name="description" content="${esc(desc)}" />
${noindex ? '<meta name="robots" content="noindex,follow" />\n' : ""}<link rel="canonical" href="${esc(canonical)}" />
<meta name="theme-color" content="#3c3a20" />
<link rel="icon" href="/assets/favicon.svg" type="image/svg+xml" />
<meta property="og:type" content="${ogType}" />
<meta property="og:site_name" content="${SITE_NAME}" />
<meta property="og:locale" content="ja_JP" />
<meta property="og:title" content="${esc(title)}" />
<meta property="og:description" content="${esc(desc)}" />
<meta property="og:url" content="${esc(canonical)}" />
<meta property="og:image" content="${esc(ogImage)}" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="${esc(title)}" />
<meta name="twitter:description" content="${esc(desc)}" />
<meta name="twitter:image" content="${esc(ogImage)}" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500;1,600&family=Shippori+Mincho:wght@500;600&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="/assets/tokens.css" />
<link rel="stylesheet" href="/works/works.css" />
${ld}`;
}

const orgJsonLd = () => ({
  "@context": "https://schema.org",
  "@type": "Organization",
  name: ORG.name,
  alternateName: ORG.alt,
  url: `${SITE_URL}/`,
  logo: `${SITE_URL}/assets/favicon.svg`,
  email: ORG.email,
  telephone: ORG.tel,
  address: {
    "@type": "PostalAddress",
    postalCode: ORG.address.postalCode,
    addressRegion: ORG.address.region,
    addressLocality: ORG.address.locality,
    streetAddress: ORG.address.street,
    addressCountry: "JP",
  },
  ...(ORG.sameAs.length ? { sameAs: ORG.sameAs } : {}),
});

const header = () => `<a class="skip" href="#main">本文へスキップ</a>
<header class="hd" data-header>
  <div class="wrap hd-in">
    <a class="logo" href="/" aria-label="LINDO ホーム">LIND<b>O</b></a>
    <nav class="nav" data-nav aria-label="メインナビゲーション">
      <a href="/">HOME</a>
      <a href="/works/" aria-current="page">WORKS</a>
      <a href="mailto:${ORG.email}">CONTACT</a>
    </nav>
    <button class="nav-toggle" data-nav-toggle aria-label="メニュー" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>`;

const footer = () => `<footer class="ft">
  <div class="wrap ft-in">
    <div>
      <p class="brand">LINDO</p>
      <p>${esc(ORG.name)}（${esc(ORG.alt)}）<br />Creative ｜ Branding ｜ Design ｜ Styling</p>
    </div>
    <div>
      <h3>Company</h3>
      <p>〒${ORG.address.postalCode}<br />${esc(ORG.address.region)}${esc(ORG.address.locality)}${esc(ORG.address.street)}</p>
    </div>
    <div>
      <h3>Contact</h3>
      <a href="tel:0353085822">tel. 03-5308-5822</a>
      <a href="mailto:${ORG.email}">${ORG.email}</a>
    </div>
  </div>
  <div class="wrap"><p class="copy">© ${new Date().getFullYear()} ${esc(ORG.name)}</p></div>
</footer>`;

const scripts = () => `<script src="/assets/reveal.js" defer></script>`;

/* ---- 一覧ページ ---- */
function renderIndex(works) {
  const cats = [...new Set(works.flatMap((w) => w.categories || []))];
  const filters = ["all", ...cats]
    .map((c, i) => {
      const label = c === "all" ? "All" : c;
      return `<button class="filter" data-filter="${esc(c)}" aria-pressed="${i === 0 ? "true" : "false"}">${esc(label)}</button>`;
    })
    .join("\n        ");

  const cards = works
    .map((w) => {
      const d = dims(w.cover);
      const dim = d ? ` width="${d.w}" height="${d.h}"` : "";
      const catSpans = (w.categories || []).map((c) => `<span>${esc(c)}</span>`).join("");
      return `<a class="card rv" href="/works/${esc(w.slug)}/" data-categories="${esc((w.categories || []).join(","))}">
          <div class="card-fig"><img src="${esc(w.cover)}" alt="${esc(w.title)}"${dim} loading="lazy" decoding="async" /></div>
          <div class="card-cats">${catSpans}</div>
          <h2>${esc(w.title)}</h2>
          <span class="yr">${esc(w.year || "")}</span>
        </a>`;
    })
    .join("\n        ");

  const head = headCommon({
    title: "WORKS | LINDO",
    desc: "株式会社LINDOの制作実績。アーティストのビジュアルコンセプト、スタイリング、ブランディング／ロゴ（B.I）の事例をご覧いただけます。",
    canonical: `${SITE_URL}/works/`,
    ogImage: `${SITE_URL}/assets/og-default.png`,
    jsonld: [orgJsonLd()],
  });

  return `<!doctype html>
<html lang="ja">
<head>
${head}
</head>
<body>
${header()}
<main id="main">
  <section class="intro">
    <div class="wrap">
      <p class="eyebrow rv">Selected Works</p>
      <h1 class="rv d1">WORKS</h1>
      <p class="rv d2">アーティストのビジュアルコンセプト、スタイリング、ブランディングまで。これまでに手がけたクリエイティブの一部をご紹介します。</p>
      <div class="filters rv d2" role="group" aria-label="カテゴリで絞り込み">
        ${filters}
      </div>
    </div>
  </section>
  <section class="wrap">
    <div class="grid">
        ${cards}
    </div>
    <p class="empty" data-empty hidden>該当する作品がありません。</p>
  </section>
</main>
${footer()}
${scripts()}
<script src="/works/works.js" defer></script>
</body>
</html>
`;
}

/* ---- 詳細ページ ---- */
function renderDetail(w, prev, next) {
  const coverAbs = absImg(w.cover);
  const seoTitle = w.seoTitle || `${w.title} | WORKS | LINDO`;
  const canonical = `${SITE_URL}/works/${w.slug}/`;

  const metaRows = [
    w.client ? ["Client", w.client] : null,
    w.year ? ["Year", w.year] : null,
    w.role ? ["Role", w.role] : null,
    (w.categories || []).length ? ["Category", w.categories.join(" / ")] : null,
  ]
    .filter(Boolean)
    .map(([k, v]) => `<div><dt>${esc(k)}</dt><dd>${esc(v)}</dd></div>`)
    .join("\n        ");

  const coverDim = dims(w.cover);
  const coverDimAttr = coverDim ? ` width="${coverDim.w}" height="${coverDim.h}"` : "";

  const bodyHtml = (w.body || w.description || "")
    .split("\n")
    .map((p) => p.trim())
    .filter(Boolean)
    .map((p) => `<p>${esc(p)}</p>`)
    .join("\n      ");

  const galleryHtml = (w.gallery || [])
    .map((src, i) => {
      const d = dims(src);
      const dim = d ? ` width="${d.w}" height="${d.h}"` : "";
      const wide = d && d.w >= d.h && (w.gallery.length % 2 === 1) && i === 0 ? " wide" : "";
      return `<figure class="rv${wide}"><img src="${esc(src)}" alt="${esc(w.title)} ギャラリー ${i + 1}"${dim} loading="lazy" decoding="async" /></figure>`;
    })
    .join("\n      ");

  const creditsHtml = (w.credits || [])
    .map((c) => `<dt>${esc(c.role)}</dt><dd>${esc(c.name)}</dd>`)
    .join("\n        ");

  const pager = `<nav class="pager" aria-label="前後の作品">
    ${prev ? `<a class="pv" href="/works/${esc(prev.slug)}/"><span class="lbl">← Prev</span>${esc(prev.title)}</a>` : "<span></span>"}
    ${next ? `<a class="nx" href="/works/${esc(next.slug)}/"><span class="lbl">Next →</span>${esc(next.title)}</a>` : ""}
  </nav>`;

  const jsonld = [
    {
      "@context": "https://schema.org",
      "@type": "CreativeWork",
      name: w.title,
      headline: w.title,
      description: w.description || "",
      image: coverAbs,
      url: canonical,
      ...(w.year ? { dateCreated: String(w.year) } : {}),
      ...(w.categories?.length ? { genre: w.categories } : {}),
      creator: { "@type": "Organization", name: ORG.name, url: `${SITE_URL}/` },
    },
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      itemListElement: [
        { "@type": "ListItem", position: 1, name: "HOME", item: `${SITE_URL}/` },
        { "@type": "ListItem", position: 2, name: "WORKS", item: `${SITE_URL}/works/` },
        { "@type": "ListItem", position: 3, name: w.title, item: canonical },
      ],
    },
  ];

  const head = headCommon({
    title: seoTitle,
    desc: w.description || w.title,
    canonical,
    ogImage: coverAbs,
    ogType: "article",
    jsonld,
  });

  return `<!doctype html>
<html lang="ja">
<head>
${head}
</head>
<body>
${header()}
<main id="main">
  <article class="wrap">
    <p class="bc"><a href="/">HOME</a> ／ <a href="/works/">WORKS</a> ／ <span>${esc(w.title)}</span></p>
    <header class="work-head">
      <h1 class="rv">${esc(w.title)}</h1>
      <dl class="work-meta rv d1">
        ${metaRows}
      </dl>
    </header>
    <figure class="cover rv d1"><img src="${esc(w.cover)}" alt="${esc(w.title)}"${coverDimAttr} fetchpriority="high" decoding="async" /></figure>

    ${bodyHtml ? `<div class="body-copy rv">\n      ${bodyHtml}\n    </div>` : ""}

    ${galleryHtml ? `<div class="gallery">\n      ${galleryHtml}\n    </div>` : ""}

    ${creditsHtml ? `<section class="credits rv">\n      <h2>Credits</h2>\n      <dl>\n        ${creditsHtml}\n      </dl>\n    </section>` : ""}

    ${pager}
  </article>
</main>
${footer()}
${scripts()}
</body>
</html>
`;
}

/* ---- sitemap ---- */
function renderSitemap(works) {
  const today = new Date().toISOString().slice(0, 10);
  const urls = [
    `${SITE_URL}/`,
    `${SITE_URL}/works/`,
    ...works.map((w) => `${SITE_URL}/works/${w.slug}/`),
  ];
  const body = urls
    .map((u) => `  <url>\n    <loc>${u}</loc>\n    <lastmod>${today}</lastmod>\n  </url>`)
    .join("\n");
  return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${body}\n</urlset>\n`;
}

/* ---- メイン ---- */
async function main() {
  const raw = await readFile(join(ROOT, "works", "works.json"), "utf8");
  const data = JSON.parse(raw);
  const works = Array.isArray(data.works) ? data.works : [];

  if (!works.length) throw new Error("works.json に作品がありません。");

  // バリデーション
  const seen = new Set();
  for (const w of works) {
    if (!w.slug || !slugRe.test(w.slug))
      throw new Error(`slug が不正です（半角英数とハイフンのみ）: ${JSON.stringify(w.slug)}`);
    if (seen.has(w.slug)) throw new Error(`slug が重複しています: ${w.slug}`);
    seen.add(w.slug);
    if (!w.title) throw new Error(`title がありません: ${w.slug}`);
    if (!w.cover) throw new Error(`cover がありません: ${w.slug}`);
  }

  // 一覧
  await writeFile(join(ROOT, "works", "index.html"), renderIndex(works), "utf8");

  // 詳細
  for (let i = 0; i < works.length; i++) {
    const w = works[i];
    const prev = works[i - 1] || null;
    const next = works[i + 1] || null;
    const dir = join(ROOT, "works", w.slug);
    await mkdir(dir, { recursive: true });
    await writeFile(join(dir, "index.html"), renderDetail(w, prev, next), "utf8");
  }

  // sitemap
  await writeFile(join(ROOT, "sitemap.xml"), renderSitemap(works), "utf8");

  console.log(`✓ WORKS生成完了: ${works.length}件`);
  console.log(`  - works/index.html`);
  works.forEach((w) => console.log(`  - works/${w.slug}/index.html`));
  console.log(`  - sitemap.xml`);
}

main().catch((err) => {
  console.error("✗ 生成エラー:", err.message);
  process.exit(1);
});
