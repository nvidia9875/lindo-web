/* =========================================================
   LINDO — ギャラリー画像のライトボックス（全画面拡大）。

   アーティスト・モーダル内の写真（.artist-modal .ig-cell）をクリックすると、
   その写真を全画面で1枚ずつ拡大表示する。←/→（またはボタン）で同じ作品グループ
   （＝同一 .ig-grid）内を巡回、ESC/背景クリック/✕で閉じる。
   タッチ端末（SP）は左右スワイプでも前後の写真へ移動できる。

   設計:
   - ネイティブ <dialog class="lightbox"> を1つだけ body 末尾に生成（兄弟トップレベル）。
     下層の artist-modal にネストしない → 端クリックで下層が誤って閉じる事故を回避。
   - フォーカストラップ・ESC はネイティブ <dialog>.showModal() 任せ。閉じたら元セルへ復帰。
   - html.is-locked は触らない（下層モーダルが維持しているスクロールロックを尊重）。
   - MV 等の外部リンクタイル（<a class="ig-cell--link">）は対象外＝通常遷移。
   - 動き＝transform/opacity のみ（CSS 側）。reduced-motion は全体規則で自動的に無効化。
   ========================================================= */
(function () {
  "use strict";

  // スワイプ確定：横移動がこの距離以上で前後の写真へ。
  var SWIPE_MIN_PX = 48;
  // ドラッグ開始のあそび：これ未満の指の movement では軸を判定しない。
  var DRAG_SLOP_PX = 6;

  var prefersReduced = !!(
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches
  );

  var dialog = null;
  var imgEl = null;
  var countEl = null;
  var prevBtn = null;
  var nextBtn = null;
  var group = []; // [{ src, alt }]
  var idx = 0;
  var lastCell = null;
  var touchStartX = 0;
  var touchStartY = 0;
  var touchOngoing = false;
  var justSwiped = false;
  var dragAxis = ""; // "h"（横ドラッグ中）| "v"（縦＝無視）| ""（未判定）
  var animating = false; // スライドアニメ中は新規ジェスチャを受けない

  function build() {
    dialog = document.createElement("dialog");
    dialog.className = "lightbox";
    dialog.setAttribute("aria-label", "写真の拡大表示");
    dialog.innerHTML =
      '<button type="button" class="lb-btn lb-close" aria-label="閉じる">✕</button>' +
      '<button type="button" class="lb-btn lb-nav lb-prev" aria-label="前の写真">‹</button>' +
      '<figure class="lb-figure"><img class="lb-img" alt="" /></figure>' +
      '<button type="button" class="lb-btn lb-nav lb-next" aria-label="次の写真">›</button>' +
      '<span class="lb-count" aria-hidden="true"></span>';
    document.body.appendChild(dialog);

    imgEl = dialog.querySelector(".lb-img");
    countEl = dialog.querySelector(".lb-count");
    prevBtn = dialog.querySelector(".lb-prev");
    nextBtn = dialog.querySelector(".lb-next");

    dialog.querySelector(".lb-close").addEventListener("click", function (e) {
      e.stopPropagation();
      close();
    });
    prevBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      step(-1);
    });
    nextBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      step(1);
    });

    // 画像・操作ボタン以外（背景/余白）クリックで閉じる。
    // スワイプ直後に合成される click では閉じない。
    dialog.addEventListener("click", function (e) {
      if (justSwiped) {
        justSwiped = false;
        return;
      }
      if (e.target === imgEl) return;
      if (e.target.closest(".lb-btn")) return;
      close();
    });

    // タッチ端末（SP）：画像を指でつかんで動かすスワイプで前後の写真へ。
    // 指の移動に画像が追従（transform のみ）→ 離した位置で「送る／戻す」を決める。
    // preventDefault しない（passive）のでスクロール等の既定動作は阻害しない。
    dialog.addEventListener(
      "touchstart",
      function (e) {
        justSwiped = false;
        dragAxis = "";
        touchOngoing =
          !animating &&
          e.touches.length === 1 && // ピンチ等の複数指は対象外。
          !e.target.closest(".lb-btn"); // ボタン上はタップ（click）に任せる。
        if (!touchOngoing) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
      },
      { passive: true }
    );
    dialog.addEventListener(
      "touchmove",
      function (e) {
        if (!touchOngoing) return;
        var t = e.touches[0];
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (!dragAxis) {
          if (Math.abs(dx) < DRAG_SLOP_PX && Math.abs(dy) < DRAG_SLOP_PX) return;
          dragAxis = Math.abs(dx) > Math.abs(dy) ? "h" : "v";
        }
        if (dragAxis !== "h" || group.length < 2 || prefersReduced) return;
        imgEl.classList.remove("is-anim");
        imgEl.style.transform = "translateX(" + dx.toFixed(1) + "px)";
      },
      { passive: true }
    );
    dialog.addEventListener(
      "touchend",
      function (e) {
        if (!touchOngoing) return;
        touchOngoing = false;
        var horizontal = dragAxis === "h";
        dragAxis = "";
        var t = e.changedTouches[0];
        var dx = t.clientX - touchStartX;
        if (!horizontal || group.length < 2) return;
        if (prefersReduced) {
          // reduced-motion：追従なし・従来どおり即切替。
          if (Math.abs(dx) >= SWIPE_MIN_PX) {
            justSwiped = true;
            step(dx < 0 ? 1 : -1);
          }
          return;
        }
        justSwiped = true; // ドラッグ後の合成 click で閉じない。
        if (Math.abs(dx) < SWIPE_MIN_PX) {
          settle(); // 距離不足：中央へ戻す。
        } else {
          slideTo(dx < 0 ? 1 : -1); // 左へ払う＝次、右へ払う＝前。
        }
      },
      { passive: true }
    );
    dialog.addEventListener(
      "touchcancel",
      function () {
        if (!touchOngoing) return;
        touchOngoing = false;
        dragAxis = "";
        settle();
      },
      { passive: true }
    );

    dialog.addEventListener("keydown", function (e) {
      if (e.key === "ArrowLeft") {
        e.preventDefault();
        step(-1);
      } else if (e.key === "ArrowRight") {
        e.preventDefault();
        step(1);
      }
    });

    // 閉じたら（ESC 含む）起点のセルへフォーカスを戻す。
    dialog.addEventListener("close", function () {
      if (lastCell && typeof lastCell.focus === "function") {
        lastCell.focus();
      }
      lastCell = null;
    });
  }

  function render() {
    var item = group[idx];
    if (!item) return;
    imgEl.src = item.src;
    imgEl.alt = item.alt || "";
    var many = group.length > 1;
    prevBtn.hidden = !many;
    nextBtn.hidden = !many;
    countEl.hidden = !many;
    if (many) {
      countEl.textContent = idx + 1 + " / " + group.length;
    }
  }

  function step(dir) {
    if (group.length < 2) return;
    idx = (idx + dir + group.length) % group.length;
    render();
  }

  /* transitionend（transition無効環境向けに timeout の保険つき）で fn を1回だけ実行。 */
  function afterSlide(fn) {
    var done = false;
    var run = function () {
      if (done) return;
      done = true;
      imgEl.removeEventListener("transitionend", run);
      fn();
    };
    imgEl.addEventListener("transitionend", run);
    window.setTimeout(run, 400);
  }

  /* ドラッグ距離不足・中断：画像を中央へ滑らかに戻す。 */
  function settle() {
    imgEl.classList.add("is-anim");
    imgEl.style.transform = "";
    afterSlide(function () {
      imgEl.classList.remove("is-anim");
    });
  }

  /* スワイプ確定：現画像を画面外へ送り出し→次画像を反対側から滑り込ませる。 */
  function slideTo(dir) {
    animating = true;
    var w = window.innerWidth;
    imgEl.classList.add("is-anim");
    imgEl.style.transform = "translateX(" + (dir === 1 ? -w : w) + "px)";
    imgEl.style.opacity = "0";
    afterSlide(function () {
      step(dir);
      imgEl.classList.remove("is-anim");
      imgEl.style.transform = "translateX(" + (dir === 1 ? w : -w) + "px)";
      // 反対側へ瞬間移動させてから合成レイヤーを確定（reflow）し、中央へ。
      void imgEl.offsetWidth;
      imgEl.classList.add("is-anim");
      imgEl.style.transform = "";
      imgEl.style.opacity = "";
      afterSlide(function () {
        imgEl.classList.remove("is-anim");
        animating = false;
      });
    });
  }

  function open(cell) {
    var grid = cell.closest(".ig-grid");
    if (!grid) return;

    // 同一グリッド内の写真セル（リンクタイル <a> は除外）を巡回対象に。
    var cells = Array.prototype.slice
      .call(grid.querySelectorAll(".ig-cell"))
      .filter(function (c) {
        return c.tagName !== "A";
      });
    if (!cells.length) return;

    group = cells.map(function (c) {
      var im = c.querySelector("img");
      return {
        src: im ? im.currentSrc || im.src : "",
        alt: im ? im.alt : "",
      };
    });
    idx = Math.max(0, cells.indexOf(cell));
    lastCell = cell;

    if (!dialog) build();
    // 前回のドラッグ/スライドの名残りを掃除してから表示。
    imgEl.classList.remove("is-anim");
    imgEl.style.transform = "";
    imgEl.style.opacity = "";
    animating = false;
    render();

    if (typeof dialog.showModal === "function") {
      dialog.showModal();
    } else {
      dialog.setAttribute("open", "");
    }
  }

  function close() {
    if (!dialog) return;
    if (typeof dialog.close === "function" && dialog.open) {
      dialog.close();
    } else {
      dialog.removeAttribute("open");
    }
  }

  // 委譲：モーダル内の写真セルのクリックでライトボックスを開く。
  document.addEventListener("click", function (e) {
    var cell = e.target.closest(".artist-modal .ig-cell");
    if (!cell) return;
    if (cell.tagName === "A") return; // 外部リンク（MV等）はそのまま遷移。
    e.preventDefault();
    open(cell);
  });
})();
