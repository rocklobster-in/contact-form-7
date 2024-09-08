( function( $ ) {

	'use strict';

	if ( typeof wpcf7 === 'undefined' || wpcf7 === null ) {
		return;
	}

	$( function() {
		$( window ).on( 'beforeunload', function( event ) {
			var changed = false;

			$( '#wpcf7-admin-form-element :input[type!="hidden"]' ).each( function() {
				if ( $( this ).is( ':checkbox, :radio' ) ) {
					if ( this.defaultChecked != $( this ).is( ':checked' ) ) {
						changed = true;
					}
				} else if ( $( this ).is( 'select' ) ) {
					$( this ).find( 'option' ).each( function() {
						if ( this.defaultSelected != $( this ).is( ':selected' ) ) {
							changed = true;
						}
					} );
				} else if ( $( this ).is( 'button' ) ) { // Tag generator buttons
					// Do nothing
				} else {
					if ( this.defaultValue != $( this ).val() ) {
						changed = true;
					}
				}
			} );

			if ( changed ) {
				event.returnValue = wpcf7.saveAlert;
				return wpcf7.saveAlert;
			}
		} );

		$( '#wpcf7-admin-form-element' ).submit( function() {
			if ( 'copy' != this.action.value ) {
				$( window ).off( 'beforeunload' );
			}

			if ( 'save' == this.action.value ) {
				$( '#publishing-action .spinner' ).addClass( 'is-active' );
			}
		} );

		$( '#wpcf7-ctct-enable-contact-list, #wpcf7-sendinblue-enable-contact-list, #wpcf7-sendinblue-enable-transactional-email' ).on( 'change', function() {
			if ( $( this ).is( ':checked' ) ) {
				$( this ).closest( 'tr' ).removeClass( 'inactive' );
			} else {
				$( this ).closest( 'tr' ).addClass( 'inactive' );
			}
		} );
	} );
} )( jQuery );
