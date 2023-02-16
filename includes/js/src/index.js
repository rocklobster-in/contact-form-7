import init from './init';
import submit from './submit';
import reset from './reset';
import validate from './validate';

if( document.readyState == 'interactive' || document.readyState == 'complete' ) {
	start_wpcf7_index();
}

document.addEventListener( 'DOMContentLoaded', event => {
	start_wpcf7_index();
} );

function start_wpcf7_index() {
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
		...( wpcf7 ?? {} ),
	};

	document.querySelectorAll(
		'.wpcf7 > form'
	).forEach( form => {
		wpcf7.init( form );
		form.closest( '.wpcf7' ).classList.replace( 'no-js', 'js' );
	} );
}
