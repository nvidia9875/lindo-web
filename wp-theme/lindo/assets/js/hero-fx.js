/* =========================================================
   LINDO — ヒーロー見出しの演出（Hero FX）。
   モード（h1 の data-hero-fx 属性。既定 "scatter"）:
     - scatter : カーソル近傍のタイルがランダムに飛び散る（＝オリジナルのシャッター）
     - off     : 演出なし（通常表示）

   各行を小さな正方形のタイルで敷き詰め（普段は隙間なく＝通常の文字）。カーソル半径内の
   タイルだけが transform で飛び散る。範囲外はそのまま。

   - デスクトップ＋マウス時のみ（hover/pointer 判定）。reduced-motion / 非対応 / off は通常表示。
   - 実テキストは維持（タイルは aria-hidden の装飾）。動き＝transform のみ（合成可能）。
   - window.LindoHeroFX.setMode(mode) でライブ切替（プレビュー比較UI用・本番でも無害）。
   ========================================================= */
(function () {
  "use strict";

  var mq = window.matchMedia;
  var prefersReduced = !!(mq && mq("(prefers-reduced-motion: reduce)").matches);
  var canHover = !!(mq && mq("(hover: hover) and (pointer: fine)").matches);

  var title = null;
  var mode = "scatter";
  var active = null; // { teardown }

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }
  function lines() {
    return title ? Array.prototype.slice.call(title.querySelectorAll("[data-line]")) : [];
  }

  /* 見出しを素の状態へ戻す（モード切替時に呼ぶ）。 */
  function resetTitle() {
    if (!title) return;
    title.classList.remove("shatter-ready");
    title.querySelectorAll(".shatter").forEach(function (n) {
      n.parentNode && n.parentNode.removeChild(n);
    });
    lines().forEach(function (t) {
      t.style.opacity = "";
    });
  }

  /* =======================================================
     MODE: scatter （カーソル近傍のタイルがランダムに飛び散る）
     ======================================================= */
  function modeScatter() {
    if (prefersReduced || !canHover) {
      return { teardown: function () {} }; // 静的表示
    }
    var lineObjs = [];
    var lastEvent = null;
    var rafPending = false;

    function buildLine(lnText) {
      var ln = lnText.parentNode;
      var w = lnText.offsetWidth;
      var h = lnText.offsetHeight;
      if (!w || !h) return;
      var CELL = Math.max(13, Math.round(h / 8));
      var R = CELL * 1.7;
      var cols = Math.ceil(w / CELL);
      var rows = Math.ceil(h / CELL);

      var layer = document.createElement("span");
      layer.className = "shatter";
      layer.setAttribute("aria-hidden", "true");

      var cells = [];
      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          var x = c * CELL;
          var y = r * CELL;
          var cell = document.createElement("span");
          cell.className = "shatter-cell";
          cell.style.left = x + "px";
          cell.style.top = y + "px";
          cell.style.width = CELL + 1 + "px";
          cell.style.height = CELL + 1 + "px";
          var ang = rand(0, Math.PI * 2);
          var dist = rand(CELL * 0.8, CELL * 2.6);
          cell.style.setProperty("--sx", (Math.cos(ang) * dist).toFixed(1) + "px");
          cell.style.setProperty("--sy", (Math.sin(ang) * dist).toFixed(1) + "px");
          cell.style.setProperty("--sr", rand(-32, 32).toFixed(1) + "deg");

          var inner = lnText.cloneNode(true);
          inner.className = "sc-inner";
          inner.removeAttribute("data-line");
          inner.style.left = -x + "px";
          inner.style.top = -y + "px";
          cell.appendChild(inner);
          layer.appendChild(cell);
          cells.push({ node: cell, cx: x + CELL / 2, cy: y + CELL / 2, on: false });
        }
      }
      ln.appendChild(layer);
      lineObjs.push({ el: ln, cells: cells, r2: R * R });
    }

    function process() {
      rafPending = false;
      var e = lastEvent;
      if (!e) return;
      for (var i = 0; i < lineObjs.length; i++) {
        var L = lineObjs[i];
        var rect = L.el.getBoundingClientRect();
        var lx = e.clientX - rect.left;
        var ly = e.clientY - rect.top;
        for (var j = 0; j < L.cells.length; j++) {
          var cell = L.cells[j];
          var dx = cell.cx - lx;
          var dy = cell.cy - ly;
          var activeCell = dx * dx + dy * dy < L.r2;
          if (activeCell !== cell.on) {
            cell.on = activeCell;
            cell.node.classList.toggle("on", activeCell);
          }
        }
      }
    }
    function onMove(e) {
      lastEvent = e;
      if (!rafPending) {
        rafPending = true;
        requestAnimationFrame(process);
      }
    }
    function clearAll() {
      for (var i = 0; i < lineObjs.length; i++) {
        var cells = lineObjs[i].cells;
        for (var j = 0; j < cells.length; j++) {
          if (cells[j].on) {
            cells[j].on = false;
            cells[j].node.classList.remove("on");
          }
        }
      }
    }

    lines().forEach(buildLine);
    title.classList.add("shatter-ready");
    title.addEventListener("pointermove", onMove);
    title.addEventListener("pointerleave", clearAll);

    return {
      teardown: function () {
        title.removeEventListener("pointermove", onMove);
        title.removeEventListener("pointerleave", clearAll);
      },
    };
  }

  /* MODE: off （演出なし） */
  function modeOff() {
    return { teardown: function () {} };
  }

  var MODES = {
    scatter: modeScatter,
    off: modeOff,
  };

  function apply(next) {
    if (!title) return;
    if (active && active.teardown) active.teardown();
    active = null;
    resetTitle();
    mode = MODES[next] ? next : "scatter";
    title.setAttribute("data-hero-fx", mode);
    active = MODES[mode]();
  }

  function readMode() {
    var v = (title.getAttribute("data-hero-fx") || "").trim().toLowerCase();
    mode = MODES[v] ? v : "scatter";
  }

  function init() {
    title = document.querySelector("[data-hero-fx]");
    if (!title) return;
    readMode();
    window.LindoHeroFX = { setMode: apply };

    var start = function () {
      apply(mode);
    };
    // Webフォント確定後に計測（タイル整列のため）。
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(start);
    } else {
      window.setTimeout(start, 300);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
