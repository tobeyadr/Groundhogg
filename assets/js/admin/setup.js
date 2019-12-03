( function ($, setup ) {

    $(function () {
        var $next = $( '.next-button' );
        $next.on( 'click', function () {
            $next.html( setup.saving_text );
            $next.addClass( 'spin' );
        } );
    });

})(jQuery, GroundhoggSetupObject );