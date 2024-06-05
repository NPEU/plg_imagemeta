const IMAGE_META = {
    open: function (btn) {
        //console.log('OPEN');

        // Get existing meta data from image:
        var img =  btn.closest('.media-browser-image').querySelector('img.image-cropped');

        var src = img.src.replace(window.location.origin, '').replace('/media/cache/com_media/thumbs', '').replace(/\?.*/, '');
        var src_b64 = btoa(src);

        // set up a request
        var request = new XMLHttpRequest();

        // keep track of the request
        request.onreadystatechange = function() {
            // check if the response data send back to us
            if(request.readyState === 4) {
                // uncomment the line below to see the request
                console.log(request);
                // check if the request is successful
                if (request.status === 200) {
                    var response = JSON.parse(request.response);
                    var copyright = response.data.copyright;
                    var converter = new showdown.Converter(),
                        copyright_html = converter.makeHtml(copyright);
                    var input = document.querySelector('#imageMetaModal .credit-input');
                    input.value = copyright;

                    var hidden_input = document.querySelector('#imageMetaModal .credit_for_image');
                    hidden_input.value = src_b64;

                    var preview = document.querySelector('#imageMetaModal .credit-preview');
                    preview.innerHTML = copyright_html;
                } else {
                    // otherwise display an error message
                    console.log ('An error occurred: ' +  request.status + ' ' + request.statusText);
                }
            }
        }

        // specify the type of request
        request.open('GET', '/plugins/system/imagemeta/ajax/image-meta.php?image=' + src_b64);
        request.send();

        //var val = input.value;
        //input.value = '';
    },

    close: function (btn) {
        //console.log('CLOSE');
        var input = btn.closest('.modal-content').querySelector('.credit-input');
        input.value = '';

        var preview = document.querySelector('#imageMetaModal .credit-preview');
        preview.innerHTML = '';
    },

    save: function (btn) {
        console.log('SAVE');
        var input = btn.closest('.modal-content').querySelector('.credit-input');
        var copyright = input.value;
        console.log(copyright);

        var hidden_input = document.querySelector('#imageMetaModal .credit_for_image');
        src_b64 = hidden_input.value;
        console.log(src_b64);

        // set up a request
        var request = new XMLHttpRequest();

        // keep track of the request
        request.onreadystatechange = function() {
            // check if the response data send back to us
            if(request.readyState === 4) {
                // uncomment the line below to see the request
                console.log(request);
                // check if the request is successful
                if (request.status === 200) {
                    //console.log('Saved');
                    Joomla.renderMessages({
                        ['message']: ['Attribution saved.']
                    }, undefined, true, 3000);
                } else {
                    // otherwise display an error message
                    console.log('An error occurred: ' + request.status + ' ' + request.statusText);
                    Joomla.renderMessages({
                        ['error']: ['Something went wrong.']
                    }, undefined, true, 3000);
                }
            }
        }

        // specify the type of request
        request.open('POST', '/plugins/system/imagemeta/ajax/image-meta.php?image=' + src_b64);
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        var data = {'copyright': copyright};
        data = Object.keys(data).map(
            function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
        ).join('&');

        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.send(data);

        /*
        request.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
        request.send(JSON.stringify({ 'copyright': copyright }));
        */

    },

    preview: function (input) {
        //console.log('PREVIEW');

        var converter = new showdown.Converter(),
            copyright_html = converter.makeHtml(input.value),
            preview = document.querySelector('#imageMetaModal .credit-preview');
        preview.innerHTML = copyright_html;
    }
};

(function () {
    var ready = function(fn) {
        if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    var image_meta = {

        init: function () {
            //console.log('Image Meta');

            const com_media = document.getElementById('com-media');
            if (com_media) {
                // Watch this for changes and then fix all visible filenames:
                const config = { attributes: false, childList: true, subtree: true };

                const callback = (mutationList, observer) => {
                    observer.disconnect();
                    var elements_1 = com_media.querySelectorAll('.media-browser-image');
                    Array.prototype.forEach.call(elements_1, function (el, i) {
                        //console.log(el.querySelector('.media-browser-image-meta'));
                        if (!el.querySelector('.media-browser-image-meta')) {
                            //console.log(el);
                            var info = el.querySelector('.media-browser-item-info');
                            //console.log(info.title);
                            var t = el.querySelector('.media-browser-actions');
                            t.insertAdjacentHTML('beforebegin', '<div class="media-browser-image-meta"><button type="button" class="action-toggle" aria-label="Manage attibution: ' + info.title + '" title="Manage attibution: ' + info.title + '" data-bs-toggle="modal" data-bs-target="#imageMetaModal" onclick="IMAGE_META.open(this);"><span class="image-browser-action  icon-copyright-h" aria-hidden="true"></span></button><!--v-if--></div>');
                            //el.innerHTML += '<div class="media-browser-image-meta"><button type="button" class="action-toggle" aria-label="Manage attibution: newcastle-at-night.jpg" title="Manage attibution: newcastle-at-night.jpg"><span class="image-browser-action  icon-copyright-h" aria-hidden="true"></span></button><!--v-if--></div>'
                            //el.innerHTML += '<div  class="media-browser-image-meta">TEST</div>';
                        }
                    });
                    observer.observe(com_media, config);
                };

                const observer = new MutationObserver(callback);
                observer.observe(com_media, config);
            }

            /*jQuery(function(){
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
                            placement:   'auto-bottom',
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

                                    // Adjust parent iframe height (bit of a hack):
                                    jQuery(window.frameElement).height(document.documentElement.scrollHeight);
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

            });*/

        }
    }

    ready(image_meta.init);
})();
