/* =========================================================
   LINDO — イントロローダー
   視覚（幕・バー）はCSSが担当。JSは:
     - カウンター 0→100% を rAF で滑らかに
     - 表示中の背景スクロールロック
     - 終了後にDOMから外して操作を妨げない
   prefers-reduced-motion ではローダーごと撤去。
   ========================================================= */
(function () {
  "use strict";

  var prefersReduced =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  var COUNT_MS = 1900; // バー(loaderFill 1.9s)と同期
  var OUT_MS = 2200; // 幕上げ(loaderOut 2.2s)完了

  function init() {
    var loader = document.querySelector("[data-loader]");
    if (!loader) return;

    if (prefersReduced) {
      if (loader.parentNode) loader.parentNode.removeChild(loader);
      return;
    }

    var num = loader.querySelector("[data-loader-num]");
    document.documentElement.classList.add("is-loading");

    var start = null;
    function tick(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / COUNT_MS, 1);
      if (num) num.textContent = Math.round(p * 100);
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);

    // 幕が上がり切るタイミングでロック解除。
    window.setTimeout(function () {
      document.documentElement.classList.remove("is-loading");
    }, OUT_MS);

    // 幕上げアニメ終了でDOMから除去（クリック透過の保険）。
    loader.addEventListener("animationend", function (e) {
      if (e.animationName && e.animationName.indexOf("loaderOut") !== -1) {
        loader.style.display = "none";
        document.documentElement.classList.remove("is-loading");
      }
    });

    // 保険：何らかで animationend が来なくても必ず消す。
    window.setTimeout(function () {
      loader.style.display = "none";
      document.documentElement.classList.remove("is-loading");
    }, OUT_MS + 600);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
