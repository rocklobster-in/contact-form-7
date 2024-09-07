const iconInCircle = icon => {
	const span = document.createElement( 'span' );

	span.classList.add( 'icon-in-circle' );
	span.setAttribute( 'aria-hidden', 'true' );
	span.append( icon );

	return span;
};


const canonicalizeName = orig => orig.replace( /[^0-9a-z]+/gi, '-' );


export {
	iconInCircle,
	canonicalizeName,
};
