import init from './init';
import submit from './submit';
import reset from './reset';
import validate from './validate';
import { apiFetch } from './api-fetch';

document.addEventListener( 'DOMContentLoaded', event => {

	if ( typeof wpcf7 === 'undefined' ) {
		console.error( "wpcf7 is not defined." );
		return;
	}

	if ( typeof wpcf7.api === 'undefined' ) {
		console.error( "wpcf7.api is not defined." );
		return;
	}

	if ( typeof window.fetch !== 'function' ) {
		console.error( "Your browser does not support window.fetch()." );
		return;
	}

	if ( typeof window.FormData !== 'function' ) {
		console.error( "Your browser does not support window.FormData()." );
		return;
	}

	if ( typeof NodeList.prototype.forEach !== 'function' ) {
		console.error( "Your browser does not support NodeList.forEach()." );
		return;
	}

	if ( typeof String.prototype.replaceAll !== 'function' ) {
		console.error( "Your browser does not support String.replaceAll()." );
		return;
	}

	wpcf7 = {
		init,
		submit,
		reset,
		validate,
		schemas: new Map(),
		...( wpcf7 ?? {} ),
	};

	document.querySelectorAll(
		'.wpcf7 > form'
	).forEach( form => {
		wpcf7.init( form );
		form.closest( '.wpcf7' ).classList.replace( 'no-js', 'js' );
	} );

	for ( const formId of wpcf7.schemas.keys() ) {
		apiFetch( {
			endpoint: `contact-forms/${ formId }/feedback/schema`,
			method: 'GET',
		} ).then( response => {
			wpcf7.schemas.set( formId, response );
		} );
	}

} );
