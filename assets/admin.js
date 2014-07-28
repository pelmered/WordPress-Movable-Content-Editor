(function ($) {
    "use strict";
    $(function () {

        $('#' + movableContentEditorOptions.editorId).show();
        $('#postdiv, #postdivrich').prependTo('#' + movableContentEditorOptions.editorId + ' .inside');

    });
}(jQuery));


