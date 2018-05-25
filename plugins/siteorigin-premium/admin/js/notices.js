jQuery( function($){
    $('#siteorigin-premium-notice .siteorigin-notice-dismiss').click( function(e){
        e.preventDefault();
        var $$ = $(this).blur();
        $.get( $$.attr('href') );

        $('#siteorigin-premium-notice').slideUp( function(){
            $(this).remove();
        } )
    } );
} );