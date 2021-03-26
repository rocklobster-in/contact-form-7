import init from './init';
import submit from './submit';
import reset from './reset';

document.addEventListener( 'DOMContentLoaded', event => {

	if ( typeof wp === 'undefined' ) {
		console.error( "wp is not defined." );
		return;
	}

	if ( typeof wp.apiFetch === 'undefined' ) {
		console.error( "wp.apiFetch is not defined." );
		return;
	}

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	if ( typeof forms.forEach !== 'function' ) {
		console.error( "Your browser doesn't support NodeList.forEach()." );
		return;
	}

	wpcf7 = {
		init,
		submit,
		reset,
		...( wpcf7 ?? {} ),
	};

	forms.forEach( form => wpcf7.init( form ) );
} );
