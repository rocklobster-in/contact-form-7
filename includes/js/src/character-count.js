import { absInt } from './utils';

export const initCharacterCount = form => {
	const updateCount = ( counter, target ) => {
		const starting = absInt( counter.getAttribute( 'data-starting-value' ) );
		const maximum = absInt( counter.getAttribute( 'data-maximum-value' ) );
		const minimum = absInt( counter.getAttribute( 'data-minimum-value' ) );

		const count = counter.classList.contains( 'down' )
			? starting - target.value.length
			: target.value.length;

		counter.setAttribute( 'data-current-value', count );
		counter.innerText = count;

		if ( maximum && maximum < target.value.length ) {
			counter.classList.add( 'too-long' );
		} else {
			counter.classList.remove( 'too-long' );
		}

		if ( minimum && target.value.length < minimum ) {
			counter.classList.add( 'too-short' );
		} else {
			counter.classList.remove( 'too-short' );
		}
	};

	const counters = form.querySelectorAll( '.wpcf7-character-count' );

	counters.forEach( counter => {
		const targetName = counter.getAttribute( 'data-target-name' );
		const target = form.querySelector( `[name="${ targetName }"]` );

		if ( target ) {
			target.value = target.defaultValue;

			updateCount( counter, target );

			if ( 'init' === form.wpcf7.status ) {
				target.addEventListener( 'keyup', event => {
					updateCount( counter, target );
				} );
			}
		}
	} );
};
