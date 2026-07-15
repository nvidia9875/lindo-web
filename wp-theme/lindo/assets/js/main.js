/* =========================================================
   LINDO — 共通フロントJS（CSP script-src 'self' 準拠）
   1) スクロールリビール（.rv に .in 付与）
   2) ヘッダーのスクロール状態（[data-header] に .scrolled）
   3) モバイルナビのトグル（[data-nav-toggle] ⇄ [data-nav]）

   いずれも transform/opacity のみ。reduced-motion では即時表示。
   ========================================================= */
(function () {
  "use strict";

  var prefersReduced =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---- 1) Scroll reveal ---- */
  function initReveal() {
    var items = document.querySelectorAll(".rv, .reveal");
    if (!items.length) return;

    if (prefersReduced || !("IntersectionObserver" in window)) {
      items.forEach(function (el) {
        el.classList.add("in");
      });
      return;
    }

    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("in");
            io.unobserve(entry.target);
          }
        });
      },
      // threshold は 0 のまま（＝1pxでも入ったら発火）。発火位置は rootMargin で作る。
      // 【重要】threshold に比率を入れてはいけない。.rv はセクションのラッパーにも付いており、
      // ビューポートより高い要素は交差比率が「実効ルート高 ÷ 要素高」を超えられないため、
      // 比率を要求すると背の高いセクションが永久に発火しない。実例: Works のラッパーは
      // SP1カラムで約4949px あり、threshold 0.14 では vh>=753px が必要だった
      // → iPhone の URLバー展開時(約660-735px)では表示されず、画面に触れて
      //   URLバーが畳まれた瞬間に初めて出る、という不具合になっていた。
      { threshold: 0, rootMargin: "0px 0px -10% 0px" }
    );

    items.forEach(function (el) {
      io.observe(el);
    });
  }

  /* ---- 2) Header scrolled state ---- */
  function initHeader() {
    var header = document.querySelector("[data-header]");
    if (!header) return;
    var onScroll = function () {
      header.classList.toggle("scrolled", window.scrollY > 24);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  /* ---- 3) Mobile nav toggle ---- */
  function initNav() {
    var toggle = document.querySelector("[data-nav-toggle]");
    var nav = document.querySelector("[data-nav]");
    if (!toggle || !nav) return;

    var setOpen = function (open) {
      nav.classList.toggle("open", open);
      toggle.setAttribute("aria-expanded", String(open));
    };

    toggle.setAttribute("aria-expanded", "false");
    toggle.addEventListener("click", function () {
      setOpen(!nav.classList.contains("open"));
    });

    nav.addEventListener("click", function (e) {
      if (e.target.closest("a")) setOpen(false);
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") setOpen(false);
    });
  }

  /* ---- 4) ロゴのマグネティック追従（カーソルへわずかに引き寄せ）----
     transform のみ＝合成可能。hover デバイス時のみ・reduced-motion は無効。
     "O" の hover 強調は CSS（.logo:hover b）側。 */
  function initLogoMagnet() {
    if (prefersReduced) return;
    if (
      !window.matchMedia ||
      !window.matchMedia("(hover: hover) and (pointer: fine)").matches
    ) {
      return;
    }
    var logo = document.querySelector(".logo");
    if (!logo) return;

    var PULL = 0.32; // 追従の強さ（控えめ）
    var raf = null;

    var apply = function (e) {
      raf = null;
      var rect = logo.getBoundingClientRect();
      var dx = e.clientX - (rect.left + rect.width / 2);
      var dy = e.clientY - (rect.top + rect.height / 2);
      logo.style.transform =
        "translate(" + (dx * PULL).toFixed(1) + "px," + (dy * PULL).toFixed(1) + "px)";
    };

    logo.addEventListener("pointermove", function (e) {
      if (!raf) raf = requestAnimationFrame(function () { apply(e); });
    });
    logo.addEventListener("pointerleave", function () {
      if (raf) cancelAnimationFrame(raf), (raf = null);
      logo.style.transform = "";
    });
  }

  function init() {
    initReveal();
    initHeader();
    initNav();
    initLogoMagnet();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
