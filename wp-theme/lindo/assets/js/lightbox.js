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

  // スワイプ判定：横移動がこの距離以上、かつ縦移動の1.5倍以上のとき。
  var SWIPE_MIN_PX = 48;

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

    // タッチ端末（SP）：左右スワイプで前後の写真へ。
    // preventDefault しない（passive）のでスクロール等の既定動作は阻害しない。
    dialog.addEventListener(
      "touchstart",
      function (e) {
        justSwiped = false;
        touchOngoing = e.touches.length === 1; // ピンチ等の複数指は対象外。
        if (!touchOngoing) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
      },
      { passive: true }
    );
    dialog.addEventListener(
      "touchend",
      function (e) {
        if (!touchOngoing) return;
        touchOngoing = false;
        var t = e.changedTouches[0];
        var dx = t.clientX - touchStartX;
        var dy = t.clientY - touchStartY;
        if (Math.abs(dx) < SWIPE_MIN_PX) return;
        if (Math.abs(dx) < Math.abs(dy) * 1.5) return; // 縦寄りの動きは無視。
        justSwiped = true;
        step(dx < 0 ? 1 : -1); // 左へ払う＝次、右へ払う＝前。
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
