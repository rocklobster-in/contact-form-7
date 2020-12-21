import { absInt } from './utils';

export default function init( form ) {
	if ( typeof window.FormData !== 'function' ) {
		return;
	}

	const formData = new FormData( form );

	form.wpcf7 = {
		id: absInt( formData.get( '_wpcf7' ) ),
		status: form.getAttribute( 'data-status' ),
		pluginVersion: formData.get( '_wpcf7_version' ),
		locale: formData.get( '_wpcf7_locale' ),
		unitTag: formData.get( '_wpcf7_unit_tag' ),
		containerPost: absInt( formData.get( '_wpcf7_container_post' ) ),
		parent: form.closest( '.wpcf7' ),
		formData,
	};

	form.querySelectorAll( '.wpcf7-submit' ).forEach( element => {
		element.insertAdjacentHTML(
			'afterend',
			'<span class="ajax-loader"></span>'
		);
	} );

	window.addEventListener( 'load', event => {
		if ( wpcf7.cached ) {
			wpcf7.refill( form );
		}
	} );

	form.addEventListener( 'submit', event => {
		if ( typeof window.FormData === 'function' ) {
			wpcf7.submit( form );
			event.preventDefault();
		}
	} );

	wpcf7.initSubmitButton( form );

	wpcf7.initCharacterCount( form );

	// Exclusive checkbox
	form.querySelectorAll( '.wpcf7-exclusive-checkbox' ).forEach( element => {
		element.addEventListener( 'change', event => {
			const nameAttr = event.target.getAttribute( 'name' );

			const siblings = form.querySelectorAll(
				`input[type="checkbox"][name="${ nameAttr }"]`
			);

			siblings.forEach( sibling => {
				if ( sibling !== event.target ) {
					sibling.checked = false;
				}
			} );
		} );
	} );
}
