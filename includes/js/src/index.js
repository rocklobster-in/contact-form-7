import init from './init';
import submit from './submit';
import setStatus from './status';

document.addEventListener( 'DOMContentLoaded', event => {
	wpcf7 = {
		init,
		submit,
		setStatus,
		...( wpcf7 ?? {} ),
	};

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	forms.forEach( form => wpcf7.init( form ) );
} );
