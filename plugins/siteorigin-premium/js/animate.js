/**
 * SiteOrigin specific animation code
 * Copyright SiteOrigin 2016
 */
jQuery( function($){

	var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';

	$('[data-so-animation]' ).each( function(){
		var $$ = $( this );
		var animation = $$.data('so-animation');

		// Set the animation duration
		var duration = parseFloat( animation.duration );
		if( ! isNaN( duration ) ) {
			$$.css( {
				'-webkit-animation-duration' : duration + 's',
				'animation-duration' : duration + 's',
			} );
		}

		var animateIn = function( repeat ){
			var doAnimation = function(){
				if( animation.hide ) {
					$$.css( 'opacity', 1 );
				}
				
				if( repeat ) {
					$$
						.removeClass( 'animated ' + animation.animation )
						.addClass( 'animated ' + animation.animation );
				}
				else {
					$$.addClass( 'animated ' + animation.animation );
				}
				$$.one( animationEnd, function () {
					$$.removeClass( 'animated ' + animation.animation );
				} )
			};

			var delay = parseFloat( animation.delay );
			if( ! isNaN( delay ) && delay > 0 ) {
				setTimeout( function(){
					doAnimation();
				}, delay * 1000 );
			}
			else {
				doAnimation();
			}
		}

		if( animation.hide ) {
			$$.css( 'opacity', 0 );
		}
		
		// Only perform animation once for now. Will add option to repeat later.
		switch( animation.event ) {
			case 'enter':
				// We need a timeout to make sure the page is setup properly
				setTimeout( function(){
					var onScreen = new OnScreen( { tolerance: parseInt( animation.offset ) } );
					onScreen.on( 'enter', animation.selector, function () {
						animateIn( false );
						onScreen.off( 'enter', animation.selector );
					} );
				}, 150 );
				break;

			case 'in':
				setTimeout( function(){
					var onScreen = new OnScreen( { tolerance: parseInt( animation.offset ) + $$.outerHeight() } );
					onScreen.on( 'enter', animation.selector, function () {
						animateIn( false );
						onScreen.off( 'enter', animation.selector );
					} );
				}, 150 );
				break;

			case 'hover':
				setTimeout( function(){
					$$.one( 'hover', function() {
						animateIn( false );
					});
				}, 150 );
				break;

			case 'slide_display':
				$$.closest('.sow-slider-image' ).on( 'sowSlideCycleAfter', function( e ){
					setTimeout( function(){
						animateIn( true );
					}, 100 );
				} );

				if( animation.hide ) {
					$$.closest('.sow-slider-image' ).on( 'sowSlideCycleBefore', function( e ){
						$$.css( 'opacity', 0 );
					} );
				}

				setTimeout( function(){
					animateIn( true );
				}, 100 );
				break;

			case 'load':
				animateIn( false );
				break;
		}
	} );

} );
