/* =========================================================
   LINDO 管理画面 — Instagram風グリッド画像ピッカー
   wp.media で複数選択し、添付IDをカンマ区切りで hidden に保存。
   ドラッグで並び替え（jQuery UI sortable）。
   ========================================================= */
(function ($) {
  "use strict";

  function initGallery(root) {
    var $root = $(root);
    var $input = $root.find("[data-gallery-input]");
    var $list = $root.find("[data-gallery-list]");
    var frame = null;

    function syncInput() {
      var ids = $list
        .children("li")
        .map(function () {
          return $(this).data("id");
        })
        .get();
      $input.val(ids.join(","));
    }

    function addItem(id, thumbUrl) {
      if ($list.find('li[data-id="' + id + '"]').length) {
        return;
      }
      var $li = $(
        '<li data-id="' +
          id +
          '"><img src="' +
          thumbUrl +
          '" alt="" />' +
          '<button type="button" class="lindo-gallery-remove" aria-label="削除">×</button></li>'
      );
      $list.append($li);
    }

    // 画像を選択 / 追加
    $root.on("click", "[data-gallery-add]", function (e) {
      e.preventDefault();
      if (frame) {
        frame.open();
        return;
      }
      frame = wp.media({
        title: "グリッド画像を選択",
        button: { text: "この画像を使う" },
        multiple: "add",
        library: { type: "image" },
      });
      frame.on("select", function () {
        var selection = frame.state().get("selection");
        selection.each(function (attachment) {
          var a = attachment.toJSON();
          var sizes = a.sizes || {};
          var thumb =
            (sizes.thumbnail && sizes.thumbnail.url) ||
            (sizes.medium && sizes.medium.url) ||
            a.url;
          addItem(a.id, thumb);
        });
        syncInput();
      });
      frame.open();
    });

    // 個別削除
    $root.on("click", ".lindo-gallery-remove", function (e) {
      e.preventDefault();
      $(this).closest("li").remove();
      syncInput();
    });

    // すべて外す
    $root.on("click", "[data-gallery-clear]", function (e) {
      e.preventDefault();
      if (window.confirm("選択中の画像をすべて外しますか？")) {
        $list.empty();
        syncInput();
      }
    });

    // 並び替え
    if ($list.sortable) {
      $list.sortable({
        items: "> li",
        cursor: "move",
        opacity: 0.7,
        update: syncInput,
      });
    }
  }

  $(function () {
    $("[data-lindo-gallery]").each(function () {
      initGallery(this);
    });
  });
})(jQuery);
