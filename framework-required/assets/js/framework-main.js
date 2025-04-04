jQuery(document).ready(function ($) {
    // Initialize the dynamic fields
    function initialize_framework_fields(container) {
        //color picker
        container.find(".quick-color").each(function () {
            if (!$(this).hasClass("wp-color-picker")) {
                $(this).wpColorPicker();
            }
        });
        //upload button
        container.find(".quick-upload-button").off("click").on("click", function (event) {
            let upload_frame;
            event.preventDefault();
            let upload_btn = $(this);
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
        //swtich checkbox
        container.find(".quick-switch").off("click").on("click", function () {
            let slider = $(this).find(".slider");
            let bool = 0;
            slider.hasClass("active") ? slider.removeClass("active") : ((bool = 1), slider.addClass("active"));
            $(this).find("input").val(bool).trigger("change");
        });
    }

    //init
    initialize_framework_fields($(document));
    //mult mode
    $(".field-group-warp").on("click", "a.add-item", function (event) {
        let mult_count = $(this).closest(".field-group-warp").find(".group-item").length + 1;
        let mult_template_load = $("#" + $(this).data("group-name"))
            .html()
            .replace(/({{i}})/g, mult_count);
        event.preventDefault();
        $(this).before(mult_template_load);

        //et $newItem = $(mult_template_load).insertBefore($(this));
        //initialize_framework_fields($newItem);
        initialize_framework_fields($(this).prev(".group-item"));
    });
    //mult mode del item
    $(".field-group-warp").on("click", "a.del-item", function () {
        $(this).closest(".group-item").remove();
    });
    //first init codemirror
    $(".codemirror-editor").find("textarea").each(function (index, textarea) {
        if (!textarea.CodeMirrorInstance) {
            textarea.CodeMirrorInstance = CodeMirror.fromTextArea(textarea, $(textarea).data("editor"));
        }
    });
});
