(function ($) {
    "use strict";
    $(function () {

        $('#' + movableContentEditor.editorId).show();
        $('#postdiv, #postdivrich').prependTo('#' + movableContentEditor.editorId + ' .inside');

    });
}(jQuery));


