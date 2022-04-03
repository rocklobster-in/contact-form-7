import { absInt } from './utils';
import { resetCaptcha, resetQuiz } from './reset';
import { apiFetch } from './api-fetch';
import { setValidationError } from './validate';

import {
	exclusiveCheckboxHelper,
	freeTextHelper,
	urlInputHelper,
	initSubmitButton,
	initCharacterCount,
} from './helpers';

export default function init( form ) {
	const formData = new FormData( form );

	form.wpcf7 = {
		id: absInt( formData.get( '_wpcf7' ) ),
		status: form.getAttribute( 'data-status' ),
		pluginVersion: formData.get( '_wpcf7_version' ),
		locale: formData.get( '_wpcf7_locale' ),
		unitTag: formData.get( '_wpcf7_unit_tag' ),
		containerPost: absInt( formData.get( '_wpcf7_container_post' ) ),
		parent: form.closest( '.wpcf7' ),
		schema: {},
	};

	form.querySelectorAll( '.has-spinner' ).forEach( element => {
		element.insertAdjacentHTML(
			'afterend',
			'<span class="wpcf7-spinner"></span>'
		);
	} );

	exclusiveCheckboxHelper( form );
	freeTextHelper( form );
	urlInputHelper( form );

	initSubmitButton( form );
	initCharacterCount( form );

	window.addEventListener( 'load', event => {
		if ( wpcf7.cached ) {
			form.reset();
		}
	} );

	form.addEventListener( 'reset', event => {
		wpcf7.reset( form );
	} );

	form.addEventListener( 'submit', event => {
		const submitter = event.submitter;
		wpcf7.submit( form, { submitter } );

		event.preventDefault();
	} );

	form.addEventListener( 'wpcf7submit', event => {
		if ( event.detail.apiResponse.captcha ) {
			resetCaptcha( form, event.detail.apiResponse.captcha );
		}

		if ( event.detail.apiResponse.quiz ) {
			resetQuiz( form, event.detail.apiResponse.quiz );
		}
	} );

	form.addEventListener( 'wpcf7reset', event => {
		if ( event.detail.apiResponse.captcha ) {
			resetCaptcha( form, event.detail.apiResponse.captcha );
		}

		if ( event.detail.apiResponse.quiz ) {
			resetQuiz( form, event.detail.apiResponse.quiz );
		}
	} );

	apiFetch( {
		endpoint: `contact-forms/${ form.wpcf7.id }/feedback/schema`,
		method: 'GET',
	} ).then( response => {
		form.wpcf7.schema = response;
	} );

	form.addEventListener( 'change', event => {
		const target = event.target;

		try {
			wpcf7.validate( form, {
				field: target.name,
			} );
		} catch ( error ) {
			setValidationError( form, {
				error_id: `${ form.wpcf7.unitTag }-ve-${ target.name }`,
				into: `span.wpcf7-form-control-wrap.${ target.name }`,
				message: error.error,
				idref: target.id,
			} );
		}
	} );
}
