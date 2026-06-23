/* =========================================================
   LINDO — ヒーロー見出しの「カーソルで壊れる」演出（3モード）
   各行を小さな正方形のウィンドウタイルで敷き詰め（普段は隙間なく＝通常の文字）。
   カーソル半径内のタイルだけが動いて分解する。範囲外はそのまま。

   モード（h1 の data-shatter 属性で指定。既定 "scatter"）:
     - scatter : 近傍タイルがランダムな方向・回転で飛び散る（離散）
     - fall    : 近傍タイルが下方向へ崩れ落ちる（重力・離散）
     - repel   : カーソルから放射状に押しのけ続ける（連続追従）

   - デスクトップ＋マウス時のみ（hover/pointer 判定）。reduced-motion / 非対応は通常表示
   - 実テキストは維持（タイルは aria-hidden の装飾）
   - 動き＝transform のみ（合成可能）。pointermove は rAF + 近傍タイルの差分更新のみ＝軽量
   - window.LindoShatter.setMode(mode) でライブ切替（プレビューの比較UI用・本番でも無害）
   ========================================================= */
(function () {
  "use strict";

  var mq = window.matchMedia;
  if (!mq) return;
  if (mq("(prefers-reduced-motion: reduce)").matches) return;
  if (!mq("(hover: hover) and (pointer: fine)").matches) return;

  var title = null;
  var mode = "scatter";
  var lineObjs = [];
  var lastEvent = null;
  var rafPending = false;

  function clearLayers() {
    if (!title) return;
    title.querySelectorAll(".shatter").forEach(function (l) {
      l.parentNode.removeChild(l);
    });
    lineObjs = [];
  }

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }

  /* モード別に「飛び散り方向」をタイルへ事前設定（repel は実行時に都度設定） */
  function presetVars(cell, CELL) {
    if (mode === "fall") {
      // 重力で落下：横ゆれ小・下方向大・回転ランダム
      cell.style.setProperty("--sx", rand(-CELL * 0.5, CELL * 0.5).toFixed(1) + "px");
      cell.style.setProperty("--sy", rand(CELL * 1.2, CELL * 3.4).toFixed(1) + "px");
      cell.style.setProperty("--sr", rand(-44, 44).toFixed(1) + "deg");
    } else if (mode === "repel") {
      cell.style.setProperty("--sx", "0px");
      cell.style.setProperty("--sy", "0px");
      cell.style.setProperty("--sr", "0deg");
    } else {
      // scatter：全方位ランダム
      var ang = rand(0, Math.PI * 2);
      var dist = rand(CELL * 0.8, CELL * 2.6);
      cell.style.setProperty("--sx", (Math.cos(ang) * dist).toFixed(1) + "px");
      cell.style.setProperty("--sy", (Math.sin(ang) * dist).toFixed(1) + "px");
      cell.style.setProperty("--sr", rand(-32, 32).toFixed(1) + "deg");
    }
  }

  function radiusForMode(CELL) {
    if (mode === "repel") return CELL * 2.8;
    if (mode === "fall") return CELL * 1.9;
    return CELL * 1.7; // scatter
  }

  function buildLine(lnText) {
    var ln = lnText.parentNode; // .ln（position: relative）
    var w = lnText.offsetWidth;
    var h = lnText.offsetHeight;
    if (!w || !h) return;

    var CELL = Math.max(13, Math.round(h / 8)); // 細かい正方形のひとマス
    var R = radiusForMode(CELL);
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
        // 全ブロック同一サイズ（端も含め均等な正方形）。+1px は継ぎ目防止。
        cell.style.width = CELL + 1 + "px";
        cell.style.height = CELL + 1 + "px";

        presetVars(cell, CELL);

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
    lineObjs.push({ el: ln, cells: cells, r2: R * R, r: R, cell: CELL });
  }

  function build() {
    clearLayers();
    var lines = title.querySelectorAll("[data-line]");
    if (!lines.length) return;
    lines.forEach(buildLine);
    title.classList.add("shatter-ready");
  }

  function processMove() {
    rafPending = false;
    var e = lastEvent;
    if (!e) return;
    var repel = mode === "repel";

    for (var i = 0; i < lineObjs.length; i++) {
      var L = lineObjs[i];
      var rect = L.el.getBoundingClientRect();
      var lx = e.clientX - rect.left;
      var ly = e.clientY - rect.top;

      for (var j = 0; j < L.cells.length; j++) {
        var cell = L.cells[j];
        var dx = cell.cx - lx;
        var dy = cell.cy - ly;
        var d2 = dx * dx + dy * dy;
        var active = d2 < L.r2;

        if (repel) {
          if (active) {
            var d = Math.sqrt(d2) || 0.0001;
            var falloff = 1 - d / L.r; // 近いほど強い 0..1
            var push = L.cell * 2 * falloff;
            cell.node.style.setProperty("--sx", ((dx / d) * push).toFixed(1) + "px");
            cell.node.style.setProperty("--sy", ((dy / d) * push).toFixed(1) + "px");
            if (!cell.on) {
              cell.on = true;
              cell.node.classList.add("on");
            }
          } else if (cell.on) {
            cell.on = false;
            cell.node.classList.remove("on");
            cell.node.style.setProperty("--sx", "0px");
            cell.node.style.setProperty("--sy", "0px");
          }
        } else if (active !== cell.on) {
          // scatter / fall は事前設定済みベクトルを .on で発火
          cell.on = active;
          cell.node.classList.toggle("on", active);
        }
      }
    }
  }

  function onMove(e) {
    lastEvent = e;
    if (!rafPending) {
      rafPending = true;
      requestAnimationFrame(processMove);
    }
  }

  function clearAll() {
    for (var i = 0; i < lineObjs.length; i++) {
      var cells = lineObjs[i].cells;
      for (var j = 0; j < cells.length; j++) {
        if (cells[j].on) {
          cells[j].on = false;
          cells[j].node.classList.remove("on");
          if (mode === "repel") {
            cells[j].node.style.setProperty("--sx", "0px");
            cells[j].node.style.setProperty("--sy", "0px");
          }
        }
      }
    }
  }

  var resizeTimer = null;
  function onResize() {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(build, 220);
  }

  function readMode() {
    var v = (title.getAttribute("data-shatter") || "").trim().toLowerCase();
    mode = v === "fall" || v === "repel" ? v : "scatter";
  }

  function setMode(next) {
    if (!title) return;
    title.setAttribute("data-shatter", next);
    readMode();
    build();
  }

  function init() {
    title = document.querySelector("[data-shatter]");
    if (!title) return;
    readMode();
    // プレビューの比較UIから呼ぶ（本番でも未使用なら無害）。
    window.LindoShatter = { setMode: setMode };

    var start = function () {
      build();
      title.addEventListener("pointermove", onMove);
      title.addEventListener("pointerleave", clearAll);
      window.addEventListener("resize", onResize);
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
