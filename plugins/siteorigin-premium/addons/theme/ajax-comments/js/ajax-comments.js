/**
 * (c) Greg Priday, freely distributable under the terms of the GPL 2.0 license.
 */
jQuery( function ( $ ) {
    var timer;
    $( 'body' ).on( 'submit', '#commentform', function () {
        var $$ = $(this);

        // Don't run this if it's in a WooCommerce form.
        if( $$.closest('.pp_woocommerce').length ) {
            return true;
        }

        // Send the comment form via AJAX
        var submitData = {};
        $( '#commentform :input' ).each( function () {
            submitData[ this.name ] = $( this ).val();
        } );
        submitData['is_ajax'] = true;
		$( '#commentform input[type="submit"]').prop( "disabled", true );
        $.ajax( $( "#commentform" ).attr( 'action' ), {
            'data':    submitData,
            'success': function ( data ) {
                if ( data.status == undefined ) {
                    return false;
                }
                else if ( data.status === 'error' ) {
                    // Workout if the error is already displaying
                    if( $( '.commentform-error' ).length && $( '<div/>' ).html(data.error).text() == $( '.commentform-error' ).html() ) {
                        var error = $( '.commentform-error' );
                        clearTimeout( timer );
                    } else {
                        var error = $( '<div class="commentform-error"></div>' );

                        // Display the error
                        error
                            .html( data.error )
                            .insertBefore( '#commentform' )
                            .hide()
                            .slideDown()
                    }
                    
                    timer = setTimeout( function() {
                        error.slideUp( function () {
                            error.remove();
                        } )
                    }, 2000 );
                }
                else if ( data.status === 'success' ) {
                    $( '#cancel-comment-reply-link' ).click();
                    $( '#single-comments-wrapper' ).html( data.html );
                }
            },
            'type':    'POST',
            'dataType':'json'
        } );

        return false;
    } );
} );
