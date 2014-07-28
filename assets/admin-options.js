(function ($) {
    "use strict";
    $(function () {

        //$('input:radio[name="' + chkboxName + '"]
        $('#movable-content-editor-specify-post-types input:checkbox').change( function() {
            
            var $this = $(this);
            console.log($(this));
            
            alert($this[0].value);
            
            $('#movable-content-editor-specify-titles').append('<input type="text" name="movable-content-editor_options[editor_headers_for_post_types][' + $this[0].value + ']"/>');
            
        });

    });
}(jQuery));
