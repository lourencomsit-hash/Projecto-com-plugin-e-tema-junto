/* Breeze Admin: Media Library image picker for destination meta box */
jQuery(function ($) {
    $(document).on('click', '.breeze-choose-image', function (e) {
        e.preventDefault();

        var $btn     = $(this);
        var $wrap    = $btn.closest('.breeze-image-field');
        var $input   = $wrap.find('input[type="url"]');
        var $preview = $wrap.find('.breeze-image-preview');
        var $img     = $preview.find('img');

        var frame = wp.media({
            title   : 'Escolher Imagem / Choose Image',
            button  : { text: 'Usar esta imagem / Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $input.val(attachment.url).trigger('change');
            $img.attr('src', attachment.url);
            $preview.show();
        });

        frame.open();
    });

    /* Remove image button */
    $(document).on('click', '.breeze-remove-image', function (e) {
        e.preventDefault();
        var $btn     = $(this);
        var $wrap    = $btn.closest('.breeze-image-field');
        var $input   = $wrap.find('input[type="url"]');
        var $preview = $wrap.find('.breeze-image-preview');
        $input.val('').trigger('change');
        $preview.hide();
    });
});
