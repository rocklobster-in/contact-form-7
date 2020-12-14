import apiFetch from '@wordpress/api-fetch';

export default function submit( form ) {
	if ( typeof window.FormData !== 'function' ) {
		return;
	}

	wpcf7.setStatus( form, 'submitting' );

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

	const setScreenReaderValidationError = error => {
		const li = document.createElement( 'li' );

		li.setAttribute( 'id', error.error_id );

		if ( error.idref ) {
			li.insertAdjacentHTML(
				'beforeend',
				`<a href="#${ error.idref }">${ error.message }</a>`
			);
		} else {
			li.insertAdjacentText(
				'beforeend',
				error.message
			);
		}

		form.wpcf7.parent.querySelector(
			'.screen-reader-response ul'
		).appendChild( li );
	};

	const setVisualValidationError = error => {
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
	};

	apiFetch( {
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/feedback`,
		method: 'POST',
		body: formData,
	} ).then( response => {
		wpcf7.setStatus( form, response.status );

		detail.status = response.status;
		detail.apiResponse = response;

		if ( response.invalid_fields ) {
			response.invalid_fields.forEach( setScreenReaderValidationError );
			response.invalid_fields.forEach( setVisualValidationError );
		}

		form.wpcf7.parent.querySelector(
			'.screen-reader-response [role="status"]'
		).insertAdjacentText( 'beforeend', response.message );

		form.querySelectorAll( '.wpcf7-response-output' ).forEach( div => {
			div.innerText = response.message;
		} );

	} ).catch( error => console.error( error ) );
}
