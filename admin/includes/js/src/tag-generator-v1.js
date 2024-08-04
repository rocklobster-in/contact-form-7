const init = form => {
	update( form );

	form.querySelectorAll(
		'.control-box'
	).forEach( box => {
		box.addEventListener( 'change', event => {
			normalize( event.target );
			update( form );
		} );
	} );
};


const update = form => {
	const id = form.dataset.id;
	const nameField = form.querySelector( '[name="name"]' );

	let name = nameField?.value.trim() ?? '';

	if ( nameField ) {
		name ||= `${ id }-${ Math.floor( Math.random() * 1000 ) }`;
		nameField.value = name;
	}

	form.querySelectorAll(
		'.tag'
	).forEach( tag => {
		let tagType = form.querySelector( '[name="tagtype"]' )?.value ||
			tag.getAttribute( 'name' );

		if ( tagType && form.querySelector( '[name="required"]:checked' ) ) {
			tagType += '*';
		}

		tag.value = compose( tagType, form );
	} );

	form.querySelectorAll(
		'span.mail-tag'
	).forEach( span => {
		span.innerText = `[${ name }]`;
	} );

	form.querySelectorAll(
		'input.mail-tag'
	).forEach( input => {
		input.value = `[${ name }]`;
	} );
};


const normalize = field => {

};


const compose = ( tagType, form ) => {
	const name = form.querySelector( '[name="name"]' )?.value ?? '';

	let composed = tagType + ' ' + name;

	return `[${ composed.trim() }]`;
};


export {
	init
};
