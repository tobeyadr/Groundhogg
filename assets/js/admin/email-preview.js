(function ($) {

    var $document = $(document);
    var $preview  = $( '#preview-modal' );

    $document.on( 'click', '.show-email-preview', function (e) {
        $preview.addClass( 'show' );
    } );

    $document.on( 'click', '#preview-popup-close, .preview-modal-content', function (e) {
        $preview.removeClass( 'show' );
    } )

})(jQuery);