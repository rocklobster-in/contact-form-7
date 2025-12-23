( function( $ ) {
	$( function() {
		var supportHtml5 = ( function() {
			var features = {};
			var input = document.createElement( 'input' );
			var inputTypes = [ 'date', 'datetime-local' ];

			$.each( inputTypes, function( index, value ) {
				input.setAttribute( 'type', value );
				features[ value ] = input.type !== 'text';
			} );

			return features;
		} )();

		if ( ! supportHtml5.date ) {
			$( 'input.wpcf7-date[type="date"]' ).each( function() {
				$( this ).datepicker( {
					dateFormat: 'yy-mm-dd',
					minDate: new Date( $( this ).attr( 'min' ) ),
					maxDate: new Date( $( this ).attr( 'max' ) )
				} );
			} );
		}

		if ( ! supportHtml5['datetime-local'] ) {
			$( 'input.wpcf7-datetime[type="datetime-local"]' ).each( function() {
				var $input = $( this );
				var $wrapper = $( '<div class="wpcf7-datetime-wrapper"></div>' );
				var $dateInput = $( '<input type="text" class="wpcf7-datetime-date" />' );
				var $timeInput = $( '<input type="time" class="wpcf7-datetime-time" />' );

				$input.before( $wrapper );
				$wrapper.append( $dateInput );
				$wrapper.append( $timeInput );
				$input.hide();

				var minAttr = $input.attr( 'min' );
				var maxAttr = $input.attr( 'max' );
				var valueAttr = $input.attr( 'value' );

				if ( valueAttr ) {
					var parts = valueAttr.split( 'T' );
					if ( parts.length === 2 ) {
						$dateInput.val( parts[0] );
						$timeInput.val( parts[1] );
					}
				}

				$dateInput.datepicker( {
					dateFormat: 'yy-mm-dd',
					minDate: minAttr ? new Date( minAttr.split( 'T' )[0] ) : null,
					maxDate: maxAttr ? new Date( maxAttr.split( 'T' )[0] ) : null,
					onSelect: function() {
						updateDateTimeValue();
					}
				} );

				function updateDateTimeValue() {
					var dateVal = $dateInput.val();
					var timeVal = $timeInput.val();

					if ( dateVal && timeVal ) {
						$input.val( dateVal + 'T' + timeVal );
					} else if ( dateVal ) {
						$input.val( dateVal + 'T00:00' );
					} else {
						$input.val( '' );
					}
				}

				$timeInput.on( 'change', updateDateTimeValue );
			} );
		}
	} );
} )( jQuery );
