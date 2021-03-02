import { absInt } from './utils';

export const exclusiveCheckboxHelper = form => {
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
};

export const freeTextHelper = form => {
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
};

export const urlInputHelper = form => {
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
};

export const initSubmitButton = form => {
	if ( ! form.querySelector( '.wpcf7-acceptance' )
	|| form.classList.contains( 'wpcf7-acceptance-as-validation' ) ) {
		return;
	}

	const checkAcceptance = () => {
		let accepted = true;

		form.querySelectorAll( '.wpcf7-acceptance' ).forEach( parent => {
			if ( ! accepted || parent.classList.contains( 'optional' ) ) {
				return;
			}

			const checkbox = parent.querySelector( 'input[type="checkbox"]' );

			if ( parent.classList.contains( 'invert' ) && checkbox.checked
			|| ! parent.classList.contains( 'invert' ) && ! checkbox.checked ) {
				accepted = false;
			}
		} );

		form.querySelectorAll( '.wpcf7-submit' ).forEach( button => {
			button.disabled = ! accepted;
		} );
	};

	checkAcceptance();

	form.addEventListener( 'change', event => {
		checkAcceptance();
	} );

	form.addEventListener( 'wpcf7reset', event => {
		checkAcceptance();
	} );
};

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

	const bulkUpdate = options => {
		options = {
			init: false,
			...options
		};

		const counters = form.querySelectorAll( '.wpcf7-character-count' );

		counters.forEach( counter => {
			const targetName = counter.getAttribute( 'data-target-name' );
			const target = form.querySelector( `[name="${ targetName }"]` );

			if ( target ) {
				target.value = target.defaultValue;

				updateCount( counter, target );

				if ( options.init ) {
					target.addEventListener( 'keyup', event => {
						updateCount( counter, target );
					} );
				}
			}
		} );
	};

	bulkUpdate( { init: true } );

	form.addEventListener( 'wpcf7reset', event => {
		bulkUpdate();
	} );
};
