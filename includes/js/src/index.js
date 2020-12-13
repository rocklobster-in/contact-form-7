import init from './init';
import submit from './submit';
import setStatus from './set-status';

document.addEventListener( 'DOMContentLoaded', event => {
	wpcf7 = {
		init,
		submit,
		setStatus,
		...( wpcf7 ?? {} ),
	};

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	forms.forEach( form => {
		form.wpcf7 = {
			id: parseInt( form.querySelector( 'input[name="_wpcf7"]' ).value, 10 ),
			status: form.getAttribute( 'data-status' ),
		};

		wpcf7.init( form );
	} );

} );
