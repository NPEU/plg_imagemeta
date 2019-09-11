(function() {
    var ready = function(fn) {
        if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

	var image_meta = {

        init: function() {

            jQuery(function(){
                // Add copyright control to media thumbnails:
                jQuery('.manager.thumbnails.thumbnails-media').each(function(){

                    var popover = [];
                    popover.push('<p><small>Accepts <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown</a></small></p>');
                    popover.push('<p><textarea class="form-control  credit-input" rows="3"></textarea></p>');
                    popover.push('<p>Preview:</p>');
                    popover.push('<div class="well credit-preview"></div>');
                    popover.push('<p class="pull-right"><button type="button" class="btn btn-default  credit-cancel">Cancel</button> <button type="button" class="btn btn-primary  credit-ok">OK</button></p>');

                    jQuery(this).find('.thumbnail:has(.imgThumb)').each(function(){
                        var $thumb = jQuery(this);

                        jQuery('<button class="btn btn-small btn-warning copyright-control">&copy;</button>')
                        .prependTo($thumb)
                        .webuiPopover({
                            title:       'Attribution (Credit line)',
                            content:     popover.join("\n"),
                            closeable:   true,
                            dismissible: true,
                            cache:       false,
                            onShow: function($element) {

                                var $img    = $thumb.find('img');
                                var src     = $img.attr('src').replace(window.location.origin, '');
                                var src_b64 = btoa(src);

                                //console.log(src);

                                // Get the image credit line if there is one:
                                jQuery.ajax({
                                    url: '/plugins/system/imagemeta/ajax/image-meta.php',
                                    data: { 'image': src_b64 },
                                    dataType: "json"
                                })
                                .done(function( response ) {
                                    copyright = response.data.copyright;

                                    var converter      = new showdown.Converter(),
                                        copyright_html = converter.makeHtml(copyright);

                                    $element.find('.credit-input')
                                    .val(copyright)
                                    .on('input', function(){
                                        $element.find('.credit-preview')
                                        .html(converter.makeHtml(jQuery(this).val()));
                                    });
                                    $element.find('.credit-preview')
                                    .html(copyright_html);
                                });



                                $element.find('.credit-cancel').click(function(){
                                    $element.hide();
                                });

                                $element.find('.credit-ok').click(function(){
                                    copyright = $element.find('.credit-input').val();

                                    // Add/Update the credit line:
                                    jQuery.ajax({
                                        url: '/plugins/system/imagemeta/ajax/image-meta.php?image=' + src_b64,
                                        method: "POST",
                                        data: { 'copyright': copyright }
                                    })
                                    .done(function( response ) {
                                        console.log( response );
                                    });

                                    $element.hide();
                                });
                            },
                        });

                    });

                });

            });

        }
	}

	ready(image_meta.init);
})();
