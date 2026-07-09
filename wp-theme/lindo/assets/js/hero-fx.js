/* =========================================================
   LINDO — ヒーロー見出しの演出（Hero FX / scatter）。

   各行を小さな正方形のタイルで敷き詰め（普段は隙間なく＝通常の文字）。カーソル半径内の
   タイルだけが transform で飛び散る（＝オリジナルのシャッター）。範囲外はそのまま。

   - デスクトップ＋マウス時のみ（hover/pointer 判定）。reduced-motion / 非対応は通常表示。
   - 実テキストは維持（タイルは aria-hidden の装飾）。動き＝transform のみ（合成可能）。
   ========================================================= */
(function () {
  "use strict";

  var mq = window.matchMedia;
  var prefersReduced = !!(mq && mq("(prefers-reduced-motion: reduce)").matches);
  var canHover = !!(mq && mq("(hover: hover) and (pointer: fine)").matches);

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }

  /* カーソル近傍のタイルがランダムに飛び散る演出を title に構築する。 */
  function initScatter(title) {
    var lines = Array.prototype.slice.call(title.querySelectorAll("[data-line]"));
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

    lines.forEach(buildLine);
    title.classList.add("shatter-ready");
    title.addEventListener("pointermove", onMove);
    title.addEventListener("pointerleave", clearAll);
  }

  function init() {
    var title = document.querySelector("[data-hero-fx]");
    if (!title) return;
    // 演出はデスクトップ＋マウス時のみ。reduced-motion / 非対応は実テキストのまま静的表示。
    if (prefersReduced || !canHover) return;

    var start = function () {
      initScatter(title);
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
