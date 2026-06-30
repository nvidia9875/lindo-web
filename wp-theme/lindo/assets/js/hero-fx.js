/* =========================================================
   LINDO — ヒーロー見出しの演出（Hero FX）。5モードを切替可能。
   各モードは「別パラダイム」の独立演出。共通の見出し DOM を使い回す。

   モード（h1 の data-hero-fx 属性。既定 "scatter"）:
     - scatter  : カーソル近傍のタイルがランダムに飛び散る（＝オリジナルのシャッター）
     - spotlight: カーソル＝柔らかい光。通過部分だけ鮮やかな版が浮かぶ（無操作時は自動往復）
     - imprint  : ロード時の一回演出。ローズの帯が走査し文字を刷り込み、最後に「.」が落ちる
     - stream   : ワードマークの大きな帯が背後を常時横に流れる（マーキー）
     - filmstrip: 文字の中を実写真がゆっくり流れる（background-clip:text）

   - 実テキストは常に DOM 維持（SEO/A11y）。動きは transform/opacity/clip-path/mask のみ。
   - prefers-reduced-motion / タッチ環境では各モードが静的フォールバック。
   - window.LindoHeroFX.setMode(mode) でライブ切替（プレビュー比較UI用・本番でも無害）。
   ========================================================= */
(function () {
  "use strict";

  var mq = window.matchMedia;
  var prefersReduced = !!(mq && mq("(prefers-reduced-motion: reduce)").matches);
  var canHover = !!(mq && mq("(hover: hover) and (pointer: fine)").matches);

  var title = null;
  var mode = "scatter";
  var active = null; // { teardown } 現在のモード

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }
  function lerp(a, b, t) {
    return a + (b - a) * t;
  }
  function lines() {
    return title ? Array.prototype.slice.call(title.querySelectorAll("[data-line]")) : [];
  }

  /* 共通：見出しを素の状態へ戻す（モード切替時に呼ぶ）。 */
  function resetTitle() {
    if (!title) return;
    title.classList.remove("shatter-ready", "is-imprint", "is-filmstrip", "is-spotlight");
    title.querySelectorAll(".shatter, .hero-fx-reveal").forEach(function (n) {
      n.parentNode && n.parentNode.removeChild(n);
    });
    var hero = title.closest(".hero");
    var stream = hero && hero.querySelector(".hero-stream");
    if (stream) stream.parentNode.removeChild(stream);
    lines().forEach(function (t) {
      t.style.opacity = "";
      t.style.backgroundImage = "";
    });
  }

  /* =======================================================
     MODE: scatter （オリジナルのタイル飛散。scatter のみ）
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

  /* =======================================================
     MODE: spotlight （カーソル＝光。通過部分が鮮やかに。無操作で自動往復）
     ======================================================= */
  function modeSpotlight() {
    title.classList.add("is-spotlight");

    // 鮮やか版のレイヤを複製してマスク（base はCSSで淡く）。
    var reveal = document.createElement("span");
    reveal.className = "hero-fx-reveal";
    reveal.setAttribute("aria-hidden", "true");
    title.querySelectorAll(".ln").forEach(function (ln) {
      reveal.appendChild(ln.cloneNode(true));
    });
    title.appendChild(reveal);

    if (prefersReduced) {
      // 静的：全面を鮮やかに（マスクなし）。
      reveal.style.webkitMaskImage = "none";
      reveal.style.maskImage = "none";
      return { teardown: function () {} };
    }

    var box = { w: 1, h: 1 };
    function measure() {
      var r = title.getBoundingClientRect();
      box.w = r.width;
      box.h = r.height;
    }
    measure();

    // 目標位置（cursor）と現在位置を lerp で滑らかに追従。無操作時は sine で往復。
    var tx = box.w * 0.5;
    var ty = box.h * 0.42;
    var cx = tx;
    var cy = ty;
    var pointerActive = false;
    var idleT = 0;
    var raf = 0;

    function onMove(e) {
      var r = title.getBoundingClientRect();
      tx = e.clientX - r.left;
      ty = e.clientY - r.top;
      pointerActive = true;
    }
    function onLeave() {
      pointerActive = false;
    }
    function tick() {
      if (!pointerActive) {
        idleT += 0.0065;
        tx = box.w * (0.5 + 0.42 * Math.sin(idleT));
        ty = box.h * (0.46 + 0.12 * Math.sin(idleT * 0.6));
      }
      cx = lerp(cx, tx, 0.12);
      cy = lerp(cy, ty, 0.12);
      reveal.style.setProperty("--mx", cx.toFixed(1) + "px");
      reveal.style.setProperty("--my", cy.toFixed(1) + "px");
      raf = requestAnimationFrame(tick);
    }

    function onResize() {
      measure();
    }
    title.addEventListener("pointermove", onMove);
    title.addEventListener("pointerleave", onLeave);
    window.addEventListener("resize", onResize);
    raf = requestAnimationFrame(tick);

    return {
      teardown: function () {
        cancelAnimationFrame(raf);
        title.removeEventListener("pointermove", onMove);
        title.removeEventListener("pointerleave", onLeave);
        window.removeEventListener("resize", onResize);
      },
    };
  }

  /* =======================================================
     MODE: imprint （ロード時の一回演出。CSS主体。切替時は再生し直す）
     ======================================================= */
  function modeImprint() {
    if (prefersReduced) {
      return { teardown: function () {} };
    }
    // クラス再付与でアニメーションを頭から再生。
    title.classList.remove("is-imprint");
    // 強制リフロー
    void title.offsetWidth;
    title.classList.add("is-imprint");
    return {
      teardown: function () {
        title.classList.remove("is-imprint");
      },
    };
  }

  /* =======================================================
     MODE: stream （ワードマークの帯が背後を横に流れる）
     ======================================================= */
  function modeStream() {
    var host = title.closest(".hero"); // ヒーロー全幅で背後に流す
    if (!host) return { teardown: function () {} };

    var band = document.createElement("div");
    band.className = "hero-stream";
    band.setAttribute("aria-hidden", "true");
    var track = document.createElement("div");
    track.className = "hero-stream-track";
    var phrase = "VISUAL CREATIVE — STYLE DIRECTION — ";
    // 2グループで -50% シームレスループ。各グループ十分な幅になるよう繰り返す。
    var groupHTML = "";
    for (var k = 0; k < 6; k++) groupHTML += "<span>" + phrase + "</span>";
    track.innerHTML = groupHTML + groupHTML;
    if (prefersReduced) track.style.animation = "none";
    band.appendChild(track);
    host.insertBefore(band, host.firstChild);

    return {
      teardown: function () {
        band.parentNode && band.parentNode.removeChild(band);
      },
    };
  }

  /* =======================================================
     MODE: filmstrip （文字の中を写真がゆっくり流れる／切替）
     画像は data-fs-images（カンマ区切りURL）。無ければ抽象グラデにフォールバック。
     ======================================================= */
  function modeFilmstrip() {
    title.classList.add("is-filmstrip");
    var raw = (title.getAttribute("data-fs-images") || "").trim();
    var imgs = raw ? raw.split(",").map(function (s) { return s.trim(); }).filter(Boolean) : [];
    var ln = lines();

    function setImg(url) {
      var css = url ? 'url("' + url + '")' : "";
      ln.forEach(function (t) {
        t.style.backgroundImage = css;
      });
    }

    if (!imgs.length) {
      // 写真未指定：ローズ→オリーブの抽象フィル（CSSの既定で表現）。
      return {
        teardown: function () {},
      };
    }

    setImg(imgs[0]);
    if (prefersReduced || imgs.length < 2) {
      return { teardown: function () {} };
    }

    // ゆっくり画像を巡回（フェードで差替え）。
    var idx = 0;
    var inner = 0;
    var timer = window.setInterval(function () {
      idx = (idx + 1) % imgs.length;
      title.classList.add("fs-fading");
      inner = window.setTimeout(function () {
        setImg(imgs[idx]);
        title.classList.remove("fs-fading");
      }, 360);
    }, 3600);

    return {
      teardown: function () {
        window.clearInterval(timer);
        window.clearTimeout(inner); // 切替後に背景を再設定してしまう内側タイマーも止める
        title.classList.remove("fs-fading");
      },
    };
  }

  var MODES = {
    scatter: modeScatter,
    spotlight: modeSpotlight,
    imprint: modeImprint,
    stream: modeStream,
    filmstrip: modeFilmstrip,
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
    // Webフォント確定後に計測（タイル整列等のため）。
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
