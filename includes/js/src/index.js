import init from './init';
import submit from './submit';
import refill from './refill';
import setStatus from './status';
import triggerEvent from './event';
import initCharacterCount from './character-count';

document.addEventListener( 'DOMContentLoaded', event => {
	wpcf7 = {
		init,
		submit,
		refill,
		setStatus,
		triggerEvent,
		initCharacterCount,
		...( wpcf7 ?? {} ),
	};

	const forms = document.querySelectorAll( '.wpcf7 > form' );

	forms.forEach( form => wpcf7.init( form ) );
} );
