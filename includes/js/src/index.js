import init from './init';
import submit from './submit';

document.addEventListener( 'DOMContentLoaded', event => {
	if ( typeof wpcf7 === 'undefined' || wpcf7 === null ) {
		return;
	}

	wpcf7 = {
		init,
		submit,
		...wpcf7,
	};

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	forms.forEach( form => {
		form.wpcf7 = {
			id: parseInt( form.querySelector( 'input[name="_wpcf7"]' ).value, 10 ),
		};

		wpcf7.init( form );
	} );

} );
