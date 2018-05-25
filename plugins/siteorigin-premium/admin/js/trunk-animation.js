jQuery( function( $ ){
	var tl = $('#toplevel_page_siteorigin .toplevel_page_siteorigin' );
	var img = tl.find( '.wp-menu-image img' );

	if( img.length ) {
		var hoverImg = $( '<img>' )
			.attr( 'src', img.attr('src' ).replace('.svg', '-hover.svg') )
			.css( 'padding-top', 3 )
			.insertAfter( img )
			.hide();

		img.data( 'src', img.attr('src') );

		// Add the hover animation
		tl.hover(
			function(){
				img.hide();
				hoverImg.show();
			},
			function(){
				img.show();
				hoverImg.hide();
			}
		);
	}


	if( $('body').hasClass('multisite') ) {
		// Multisite handles menus slightly differently
		var top = $('#toplevel_page_siteorigin');
		top.find( 'ul > li > a[href="siteorigin"]' ).closest( 'li' ).remove();
		top.find( 'a[href="siteorigin"]' ).attr( 'href', top.find( 'ul > li > a' ).attr('href') );
	}
} ) ;