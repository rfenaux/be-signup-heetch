/* globals jQuery, pikaday */

jQuery( function ( $ ) {

	$( '.datepicker-container' ).each( function ( index, element ) {
		var $datepickerContainer = $(element);
		var $datepicker = $datepickerContainer.find( '.so-premium-datepicker' );
		var options = $datepicker.data( 'options' );
		var $valInput = $datepickerContainer.siblings( '.so-contactform-datetime' );
		var defaultDate = $valInput.val() ? new Date( $valInput.val() ) : new Date();
		
		var updateDate = function () {
			var date = $datepicker.data( 'pikaday' ).getDate();
			var $timepicker = $datepickerContainer.siblings( '.timepicker-container' ).find( '.so-premium-timepicker' );
			if ( $timepicker.length > 0 ) {
				var time = $timepicker.timepicker( 'getTime' );
				if ( time && time instanceof Date ) {
					date.setHours( time.getHours(), time.getMinutes(), time.getSeconds(), time.getMilliseconds() );
					$valInput.val( date );
				}
			} else {
				$valInput.val( date );
			}
		};
		$datepicker.pikaday( {
			defaultDate: defaultDate,
			setDefaultDate: true,
			onSelect: updateDate,
			disableWeekends: options.disableWeekends,
			disableDayFn: function( date ) {
				var isDisabledDay = options.disabled.days.indexOf(date.getDay().toString()) > -1;
				if(isDisabledDay) {
					return true;
				}
				var disabled = options.disabled.dates.some(function (epoch) {
					var d = new Date(epoch);
					return d.getFullYear() === date.getFullYear() &&
						d.getMonth() === date.getMonth() &&
						d.getDate() === date.getDate();
				});
				
				return disabled;
			}
		} );
		updateDate();
	} );

	$( '.timepicker-container' ).each( function ( index, element ) {
		var $timepickerContainer = $( element );
		var $timepicker = $timepickerContainer.find('.so-premium-timepicker');
		var options = $timepicker.data('options');
		$timepicker.timepicker(options);
		var $valInput = $timepickerContainer.siblings( '.so-contactform-datetime' );
		var defaultTime = $valInput.val() ? new Date( $valInput.val() ) : new Date();
		
		// If it's not a valid date, assume it's just a time string, e.g. '12:30pm'
		if ( isNaN( defaultTime.valueOf() ) ) {
			$timepicker.val( $valInput.val() );
		} else {
			$timepicker.timepicker( 'setTime', defaultTime );
		}
		
		var updateTime = function () {
			var $datepicker = $timepickerContainer.siblings( '.datepicker-container' ).find( '.so-premium-datepicker' );
			// If we have a datepicker too, then set the time on the datepicker's selected date.
			if ( $datepicker.length > 0 ) {
				var date = $datepicker.data( 'pikaday' ).getDate();
				if ( date ) {
					var time = $timepicker.timepicker( 'getTime' );
					time = time || defaultTime;
					time = new Date( date.setHours(
						time.getHours(),
						time.getMinutes(),
						time.getSeconds(),
						time.getMilliseconds()
					) );
					$valInput.val( time );
				}
			} else {
				$valInput.val( $timepicker.val() );
			}
		};
		
		$timepicker.on( 'changeTime', updateTime );
		
		updateTime();
	} );

} );

