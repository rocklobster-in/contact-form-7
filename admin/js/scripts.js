( function( $ ) {

	'use strict';

	if ( typeof wpcf7 === 'undefined' || wpcf7 === null ) {
		return;
	}

	$( function() {

		$( '#wpcf7-admin-form-element' ).submit( function() {
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
