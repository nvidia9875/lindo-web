/* =========================================================
   LINDO — 共通フロントJS（外部化：CSP script-src 'self' 準拠）
   1) スクロールリビール（.rv / .reveal に .in を付与）
   2) ヘッダーのスクロール状態（[data-header] に .scrolled）
   3) モバイルナビのトグル（[data-nav-toggle] ⇄ [data-nav]）

   prefers-reduced-motion: reduce のときはアニメを行わず即時表示。
   ========================================================= */
(function () {
  "use strict";

  const prefersReduced =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---- 1) Scroll reveal ---- */
  function initReveal() {
    const items = document.querySelectorAll(".rv, .reveal");
    if (!items.length) return;

    if (prefersReduced || !("IntersectionObserver" in window)) {
      items.forEach((el) => el.classList.add("in"));
      return;
    }

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("in");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.14, rootMargin: "0px 0px -8% 0px" }
    );

    items.forEach((el) => io.observe(el));
  }

  /* ---- 2) Header scrolled state ---- */
  function initHeader() {
    const header = document.querySelector("[data-header]");
    if (!header) return;
    const onScroll = () => {
      header.classList.toggle("scrolled", window.scrollY > 24);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---- 3) Mobile nav toggle ---- */
  function initNav() {
    const toggle = document.querySelector("[data-nav-toggle]");
    const nav = document.querySelector("[data-nav]");
    if (!toggle || !nav) return;

    const setOpen = (open) => {
      nav.classList.toggle("open", open);
      toggle.setAttribute("aria-expanded", String(open));
      document.documentElement.classList.toggle("nav-open", open);
    };

    toggle.setAttribute("aria-expanded", "false");
    toggle.addEventListener("click", () => {
      setOpen(!nav.classList.contains("open"));
    });

    // リンク選択でクローズ / Escでクローズ
    nav.addEventListener("click", (e) => {
      if (e.target.closest("a")) setOpen(false);
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") setOpen(false);
    });
  }

  function init() {
    initReveal();
    initHeader();
    initNav();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
