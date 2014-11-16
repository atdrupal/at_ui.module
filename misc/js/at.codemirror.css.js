(function ($, Drupal, CodeMirror) {

    Drupal.behaviors.atuiDisplayFile = {
        attach: function () {
            CodeMirror.fromTextArea(document.getElementById("edit-code"), {
                lineNumbers: true
                , viewportMargin: Infinity
                , readOnly: true
                , theme: "monokai"
                , mode: "text/css"
            });
        }
    };

})(jQuery, Drupal, CodeMirror);
