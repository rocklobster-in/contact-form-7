const iconInCircle = icon => {
	const span = document.createElement( 'span' );

	span.classList.add( 'icon-in-circle' );
	span.setAttribute( 'aria-hidden', 'true' );
	span.append( icon );

	return span;
};


const canonicalizeName = orig => orig.replace( /[^0-9a-z]+/gi, '-' );


const toggleFieldset = toggle => {
	const target = document.querySelector( `#${ toggle.dataset?.toggle }` );

	if ( ! target ) {
		return;
	}

	if ( toggle.checked ) {
		target.classList.remove( 'hidden' );
	} else {
		target.classList.add( 'hidden' );
	}
};


export {
	iconInCircle,
	canonicalizeName,
	toggleFieldset,
};
