/* =========================================================
   LINDO — WORKS 一覧のカテゴリフィルタ（段階的強化）
   JSが無くても全カードは表示される（生成時に静的描画済み）。
   各カードの data-categories（カンマ区切り）で絞り込む。
   ========================================================= */
(function () {
  "use strict";

  const filters = document.querySelectorAll("[data-filter]");
  const cards = document.querySelectorAll("[data-categories]");
  const empty = document.querySelector("[data-empty]");
  if (!filters.length || !cards.length) return;

  function apply(category) {
    let visible = 0;
    cards.forEach((card) => {
      const cats = (card.getAttribute("data-categories") || "").split(",");
      const show = category === "all" || cats.includes(category);
      card.classList.toggle("is-hidden", !show);
      if (show) visible++;
    });
    if (empty) empty.hidden = visible !== 0;
  }

  filters.forEach((btn) => {
    btn.addEventListener("click", () => {
      filters.forEach((b) => b.setAttribute("aria-pressed", "false"));
      btn.setAttribute("aria-pressed", "true");
      apply(btn.getAttribute("data-filter"));
    });
  });
})();
