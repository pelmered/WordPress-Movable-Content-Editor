(function ($) {
    "use strict";
    $(function () {

        console.log(movableContentOptions.titles);
        //$('input:radio[name="' + chkboxName + '"]
        $('#movable-content-editor-specify-post-types input:checkbox').change( function() {
            
            var $this = $(this),
                $container = $('#movable-content-editor-specify-titles'),
                postType = $this[0].value,
                input = $container.find('#post-type-' + postType);
            
            if( input.length > 0 )
            {
                input.remove();
            }
            else
            {
                $container.append(
                    '<label  id="post-type-' + postType + '" class="post-type"> <span>' + postType.replace(/^./, function (match) {
                        return match.toUpperCase();
                    }) + '</span>' + 
                    '<input type="text" name="movable-content-editor_options[editor_headers_for_post_types][' + postType + ']" value="' + (movableContentOptions.titles[postType] ? movableContentOptions.titles[postType] : '') + '" />' +
                    '</label>'
                ); 
        
                
            }
            
        });

    });
}(jQuery));
