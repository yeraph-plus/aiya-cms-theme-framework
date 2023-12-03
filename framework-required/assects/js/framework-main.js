jQuery(document).ready(function ($) {
  //color-picker
  $(".framework-color-picker").find(".quick-input").wpColorPicker();

  //upload
  $(".framework-upload").on("click", "a.quick-upload-button", function () {
    var upload_frame;
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
      var attachment = upload_frame.state().get("selection").first().toJSON();

      upload_btn
        .parent()
        .find(".quick-upload-input")
        .val(attachment.url)
        .trigger("change");
    });

    upload_frame.open();
  });
  //swtich
  $(".framework-switcher").on("click", ".quick-switch", function () {
    var slider = $(this).find(".slider");
    var bool = 0;
    slider.hasClass("active")
      ? slider.removeClass("active")
      : ((bool = 1), slider.addClass("active")),
      $(this).find("input").val(bool).trigger("change");
  });
  $(".codemirror-editor").ready(function () {
    return this.each(function () {
      var t, i, e, s, c;
      "function" == typeof CodeMirror &&
        ((t = S(this)),
        (i = t.find("textarea")),
        (e = t.find(".CodeMirror")),
        (s = i.data("editor")),
        e.length && e.remove(),
        (c = setInterval(function () {
          var n, e;
          t.is(":visible") &&
            ((n = CodeMirror.fromTextArea(i[0], s)),
            "default" !== s.theme &&
              -1 === j.vars.code_themes.indexOf(s.theme) &&
              ((e = S("<link>")),
              S("#csf-codemirror-css").after(e),
              e.attr({
                rel: "stylesheet",
                id: "csf-codemirror-" + s.theme + "-css",
                href: s.cdnURL + "/theme/" + s.theme + ".min.css",
                type: "text/css",
                media: "all",
              }),
              j.vars.code_themes.push(s.theme)),
            (CodeMirror.modeURL = s.cdnURL + "/mode/%N/%N.min.js"),
            CodeMirror.autoLoadMode(n, s.mode),
            n.on("change", function (e, t) {
              i.val(n.getValue()).trigger("change");
            }),
            clearInterval(c));
        })));
    });
  });
});
