/* =========================================================
   LINDO — アーティスト・モーダル
   native <dialog> ベース。フォーカストラップ/ESCはブラウザ標準。
   ここでは:
     - トリガ([data-modal-target]) → dialog.showModal()
     - 背景(::backdrop)クリックで閉じる
     - 閉じるボタン([data-modal-close])
     - 背景スクロールのロック / 解除
     - 閉じたらトリガにフォーカスを戻す
   showModal 非対応ブラウザでは対象セクションへスクロール（内容はDOMに実在）。
   ========================================================= */
(function () {
  "use strict";

  var lastTrigger = null;

  function lockScroll(lock) {
    document.documentElement.classList.toggle("is-locked", lock);
  }

  function openModal(dialog, trigger) {
    if (!dialog) return;

    // 非対応環境のフォールバック：内容へスクロール。
    if (typeof dialog.showModal !== "function") {
      dialog.setAttribute("open", "");
      dialog.scrollIntoView({ behavior: "smooth", block: "start" });
      return;
    }

    lastTrigger = trigger || null;
    dialog.showModal();
    lockScroll(true);

    // 開いたらスクロール位置を先頭へ。
    var scroller = dialog.querySelector("[data-modal-scroll]");
    if (scroller) scroller.scrollTop = 0;
  }

  function closeModal(dialog) {
    if (!dialog) return;
    if (typeof dialog.close === "function" && dialog.open) {
      dialog.close();
    } else {
      dialog.removeAttribute("open");
    }
  }

  function onTriggerClick(e) {
    var trigger = e.target.closest("[data-modal-target]");
    if (!trigger) return;
    var id = trigger.getAttribute("data-modal-target");
    var dialog = document.getElementById(id);
    if (!dialog) return;
    e.preventDefault();
    openModal(dialog, trigger);
  }

  function initDialog(dialog) {
    // 閉じるボタン。
    dialog.addEventListener("click", function (e) {
      if (e.target.closest("[data-modal-close]")) {
        e.preventDefault();
        closeModal(dialog);
        return;
      }
      // 背景(::backdrop)クリック判定：ダイアログ矩形の外。
      var rect = dialog.getBoundingClientRect();
      var inside =
        e.clientX >= rect.left &&
        e.clientX <= rect.right &&
        e.clientY >= rect.top &&
        e.clientY <= rect.bottom;
      if (!inside) {
        closeModal(dialog);
      }
    });

    // 閉じたら（ESC含む）スクロール解除＋フォーカス復帰。
    dialog.addEventListener("close", function () {
      lockScroll(false);
      if (lastTrigger && typeof lastTrigger.focus === "function") {
        lastTrigger.focus();
        lastTrigger = null;
      }
    });
  }

  function init() {
    document.addEventListener("click", onTriggerClick);
    var dialogs = document.querySelectorAll(".artist-modal");
    dialogs.forEach(function (d) {
      initDialog(d);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
