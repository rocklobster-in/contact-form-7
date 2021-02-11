import apiFetch from '@wordpress/api-fetch';

import { setStatus } from './status';
import { triggerEvent } from './event';

export default function submit( form, options = {} ) {
	const formData = new FormData( form );

	if ( options.submitter && options.submitter.name ) {
		formData.append( options.submitter.name, options.submitter.value );
	}

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

		if ( control.closest( '.use-floating-validation-tip' ) ) {
			control.addEventListener( 'focus', event => {
				tip.setAttribute( 'style', 'display: none' );
			} );

			tip.addEventListener( 'mouseover', event => {
				tip.setAttribute( 'style', 'display: none' );
			} );
		}
	};

	apiFetch( {
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/feedback`,
		method: 'POST',
		body: formData,
		wpcf7: {
			endpoint: 'feedback',
			form,
			detail,
		},
	} ).then( response => {

		const status = setStatus( form, response.status );

		detail.status = response.status;
		detail.apiResponse = response;

		if ( [ 'invalid', 'unaccepted', 'spam', 'aborted' ].includes( status ) ) {
			triggerEvent( form, status, detail );
		} else if ( [ 'sent', 'failed' ].includes( status ) ) {
			triggerEvent( form, `mail${ status }`, detail );
		}

		triggerEvent( form, 'submit', detail );

		return response;

	} ).then( response => {

		if ( response.posted_data_hash ) {
			form.querySelector(
				'input[name="_wpcf7_posted_data_hash"]'
			).value = response.posted_data_hash;
		}

		if ( 'mail_sent' === response.status ) {
			form.reset();
		}

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

apiFetch.use( ( options, next ) => {
	if ( options.wpcf7 && 'feedback' === options.wpcf7.endpoint ) {
		const { form, detail } = options.wpcf7;

		clearResponse( form );
		triggerEvent( form, 'beforesubmit', detail );
		setStatus( form, 'submitting' );
	}

	return next( options );
} );

export const clearResponse = form => {
	form.wpcf7.parent.querySelector(
		'.screen-reader-response [role="status"]'
	).innerText = '';

	form.wpcf7.parent.querySelector(
		'.screen-reader-response ul'
	).innerText = '';

	form.querySelectorAll( '.wpcf7-not-valid-tip' ).forEach( span => {
		span.remove();
	} );

	form.querySelectorAll( '.wpcf7-form-control' ).forEach( control => {
		control.setAttribute( 'aria-invalid', 'false' );
		control.removeAttribute( 'aria-describedby' );
		control.classList.remove( 'wpcf7-not-valid' );
	} );

	form.querySelectorAll( '.wpcf7-response-output' ).forEach( div => {
		div.innerText = '';
	} );
};
