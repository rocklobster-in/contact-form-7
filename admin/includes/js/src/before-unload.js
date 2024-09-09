const init = () => {
	const form = document.querySelector( '#wpcf7-admin-form-element' );

	if ( ! form ) {
		return;
	}

	window.addEventListener( 'beforeunload', beforeUnloadHandler );

	form.addEventListener( 'submit', event => {
		if ( ! event.submitter?.name || 'wpcf7-copy' !== event.submitter.name ) {
			window.removeEventListener( 'beforeunload', beforeUnloadHandler );
		}
	} );
};


const beforeUnloadHandler = event => {
	const form = document.querySelector( '#wpcf7-admin-form-element' );

	if ( ! form ) {
		return;
	}

	let changed = false;

	form.querySelectorAll(
		'input, textarea, select'
	).forEach( field => {
		if ( changed ) {
			return;
		}

		switch ( field.type ) {
			case 'button':
			case 'hidden':
			case 'image':
			case 'reset':
			case 'search':
			case 'submit':
				break; // Do nothing.
			case 'checkbox':
			case 'radio':
				changed = field.defaultChecked !== field.checked;
				break;
			case 'select-multiple':
			case 'select-one':
				field.querySelectorAll( 'option' ).forEach( opt => {
					if ( ! changed && opt.defaultSelected !== opt.selected ) {
						changed = true;
					}
				} );

				break;
			default:
				changed = field.defaultValue !== field.value;
		}
	} );

	if ( changed ) {
		event.preventDefault();
	}
};


export {
	init,
};
