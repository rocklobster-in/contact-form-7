import { absInt } from './utils';
import { resetCaptcha, resetQuiz } from './reset';

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
		get schema() {
			return wpcf7.schemas.get( this.id );
		},
	};

	wpcf7.schemas.set( form.wpcf7.id, undefined );

	// Add hidden field for current page URL
	// This provides a reliable fallback for [_url] special mail tag
	// when HTTP_REFERER is not available due to referrer policies
	let pageUrlField = form.querySelector( 'input[name="_wpcf7_page_url"]' );

	if ( ! pageUrlField ) {
		pageUrlField = document.createElement( 'input' );
		pageUrlField.type = 'hidden';
		pageUrlField.name = '_wpcf7_page_url';
		pageUrlField.value = window.location.href;
		form.appendChild( pageUrlField );
	} else {
		pageUrlField.value = window.location.href;
	}

	// Update page URL on SPA navigation
	const originalPushState = history.pushState;
	const originalReplaceState = history.replaceState;

	history.pushState = function() {
		originalPushState.apply( this, arguments );
		updatePageUrlField( form );
	};

	history.replaceState = function() {
		originalReplaceState.apply( this, arguments );
		updatePageUrlField( form );
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
		wpcf7.submit( form, { submitter: event.submitter } );
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

	form.addEventListener( 'change', event => {
		if ( event.target.closest( '.wpcf7-form-control' ) ) {
			wpcf7.validate( form, { target: event.target } );
		}
	} );

	form.addEventListener( 'wpcf7statuschanged', event => {
		const status = event.detail.status;

		form.querySelectorAll( '.active-on-any' ).forEach( elm => {
			elm.removeAttribute( 'inert' );
			elm.classList.remove( 'active-on-any' );
		} );

		form.querySelectorAll( `.inert-on-${ status }` ).forEach( elm => {
			elm.setAttribute( 'inert', 'inert' );
			elm.classList.add( 'active-on-any' );
		} );
	} );
}

// Helper function to update page URL field
function updatePageUrlField( form ) {
	const pageUrlField = form.querySelector( 'input[name="_wpcf7_page_url"]' );

	if ( pageUrlField ) {
		pageUrlField.value = window.location.href;
	}
}
