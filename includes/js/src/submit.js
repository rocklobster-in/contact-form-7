import apiFetch from '@wordpress/api-fetch';

export default function submit( form ) {
	if ( typeof window.FormData !== 'function' ) {
		return;
	}

	const formData = new FormData( form );

	const detail = {
		contactFormId: form.wpcf7.id,
		pluginVersion: form.wpcf7.pluginVersion,
		contactFormLocale: form.wpcf7.locale,
		unitTag: form.wpcf7.unitTag,
		containerPostId: form.wpcf7.containerPost,
		status: form.wpcf7.status,
		inputs: Array.from(
			formData,
			val => {
				const name = val[0], value = val[1];
				return name.match( /^_/ ) ? false : { name, value };
			}
		).filter( val => false !== val ),
		formData,
	};

	wpcf7.setStatus( form, 'submitting' );

	apiFetch( {
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/feedback`,
		method: 'POST',
		body: formData,
	} ).then( response => {
		detail.status = response.status;
		detail.apiResponse = response;

		wpcf7.setStatus( form, response.status );

		if ( response.invalid_fields ) {
			response.invalid_fields.forEach( error => {
				const wrap = form.querySelector( error.into );

				const control = wrap.querySelector( '.wpcf7-form-control' );
				control.classList.add( 'wpcf7-not-valid' );
				control.setAttribute( 'aria-invalid', 'true' );
				control.setAttribute( 'aria-describedby', error.error_id );

				const tip = document.createElement( 'span' );
				tip.setAttribute( 'class', 'wpcf7-not-valid-tip' );
				tip.setAttribute( 'aria-hidden', 'true' );
				tip.insertAdjacentText( 'beforeend', error.message );
				wrap.appendChild( tip );
			} );
		}

		form.querySelectorAll( '.wpcf7-response-output' ).forEach( div => {
			div.innerText = response.message;
		} );
	} );
}
