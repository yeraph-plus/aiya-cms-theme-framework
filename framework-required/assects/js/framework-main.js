jQuery(document).ready(function ($) {
  //color-picker
  $(".framework-color-picker").ready(function () {
    $(this).find(".quick-color").wpColorPicker();
  });
  //upload
  $(".framework-upload").on("click", "a.quick-upload-button", function () {
    let upload_frame;
    event.preventDefault();
    upload_btn = $(this);
    if (upload_frame) {
      upload_frame.open();
      return;
    }
    upload_frame = wp.media({
      multiple: false,
    });
    upload_frame.on("select", function () {
      let attachment = upload_frame.state().get("selection").first().toJSON();

      upload_btn.parent().find(".quick-upload-input").val(attachment.url).trigger("change");
    });
    upload_frame.open();
  });
  //swtich
  $(".framework-switcher").on("click", ".quick-switch", function () {
    let slider = $(this).find(".slider");
    let bool = 0;
    slider.hasClass("active") ? slider.removeClass("active") : ((bool = 1), slider.addClass("active")), $(this).find("input").val(bool).trigger("change");
  });
  //mult mode
  $(".framework-field-mult").ready(function () {
    $(".field-group-warp").on("click", "a.add-item", function () {
      event.preventDefault();
      mult_self = $(this).closest(".field-group-warp");
      mult_name = $(this).data("group-name");
      mult_format = $("#" + mult_name).html();
      count = mult_self.find(".group-item").length + 1;
      add_template = mult_format.replace(/({{i}})/g, count);
      $(this).before(add_template);
    });
    $(".field-group-warp .group-item").on("click", "a.del-item", function () {
      event.preventDefault();
      mult_self = $(this).closest(".group-item");
      $(this).closest(".group-item").remove();
    });
  });
  //codemirror
  $(".codemirror-editor").ready(function () {
    let find_editor = $(this).find(".codemirror-editor").find("textarea");
    load = setInterval(function () {
      find_editor.each(function (i, e) {
        let id = $(e).attr("id");
        let attr = $(e).data("editor");
        let editor = CodeMirror.fromTextArea(document.getElementById(id), attr);
      });
      clearInterval(load);
    });
  });
});
