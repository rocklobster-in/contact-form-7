import { absInt } from './utils';
import { clearResponse } from './submit';
import { refillCaptcha, refillQuiz } from './refill';

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
	};

	form.querySelectorAll( '.wpcf7-submit' ).forEach( element => {
		element.insertAdjacentHTML(
			'afterend',
			'<span class="ajax-loader"></span>'
		);
	} );

	window.addEventListener( 'load', event => {
		if ( wpcf7.cached ) {
			form.reset();
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

	form.addEventListener( 'reset', event => {
		wpcf7.setStatus( form, 'resetting' );

		clearResponse( form );
		wpcf7.initSubmitButton( form );
		wpcf7.initCharacterCount( form );
		wpcf7.refill( form );

		wpcf7.setStatus( form, 'init' );
	} );

	form.wpcf7.parent.addEventListener( 'wpcf7submit', event => {
		if ( event.detail.apiResponse.captcha ) {
			refillCaptcha( form, event.detail.apiResponse.captcha );
		}

		if ( event.detail.apiResponse.quiz ) {
			refillQuiz( form, event.detail.apiResponse.quiz );
		}
	} );

	form.wpcf7.parent.addEventListener( 'wpcf7refill', event => {
		if ( event.detail.apiResponse.captcha ) {
			refillCaptcha( form, event.detail.apiResponse.captcha );
		}

		if ( event.detail.apiResponse.quiz ) {
			refillQuiz( form, event.detail.apiResponse.quiz );
		}
	} );

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

	// Free text option for checkboxes and radio buttons
	form.querySelectorAll( '.has-free-text' ).forEach( element => {
		const freetext = element.querySelector( 'input.wpcf7-free-text' );

		const checkbox = element.querySelector(
			'input[type="checkbox"], input[type="radio"]'
		);

		freetext.disabled = ! checkbox.checked;

		form.addEventListener( 'change', event => {
			freetext.disabled = ! checkbox.checked;

			if ( event.target === checkbox && checkbox.checked ) {
				freetext.focus();
			}
		} );
	} );

	// URL input correction
	form.querySelectorAll( '.wpcf7-validates-as-url' ).forEach( element => {
		element.addEventListener( 'change', event => {
			let val = element.value.trim();

			if ( val
			&& ! val.match( /^[a-z][a-z0-9.+-]*:/i )
			&& -1 !== val.indexOf( '.' ) ) {
				val = val.replace( /^\/+/, '' );
				val = 'http://' + val;
			}

			element.value = val;
		} );
	} );
}
