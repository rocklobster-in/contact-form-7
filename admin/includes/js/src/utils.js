const iconInCircle = icon => {
	const span = document.createElement( 'span' );

	span.classList.add( 'icon-in-circle' );
	span.setAttribute( 'aria-hidden', 'true' );
	span.append( icon );

	return span;
};


export {
	iconInCircle,
};
