export const triggerEvent = ( target, name, detail ) => {
	const event = new CustomEvent( `wpcf7${ name }`, {
		bubbles: true,
		detail,
	} );

	if ( typeof target === 'string' ) {
		target = document.querySelector( target );
	}

	target.dispatchEvent( event );
};
