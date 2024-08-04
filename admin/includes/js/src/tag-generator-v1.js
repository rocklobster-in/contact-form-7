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
		const basetype = form.querySelector( '[name="tagtype"]' )?.value ||
			tag.name;

		if ( basetype ) {
			tag.value = compose( basetype, form );
		}
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


const compose = ( basetype, form ) => {
	const name = form.querySelector( '[name="name"]' )?.value ?? '';
	const scope = form.querySelector( `.scope.${ basetype }` ) ?? form;

	const options = [];

	scope.querySelectorAll(
		'.option'
	).forEach( input => {
		if ( 'checkbox' === input.type ) {
			if ( input.checked ) {
				options.push( input.name );
			}
		} else if ( 'radio' === input.type ) {
			if ( input.checked && ! input.classList.contains( 'default' ) ) {
				options.push( `${ input.name }:${ input.value }` );
			}
		} else if ( '' !== input.value ) {
			if ( input.classList.contains( 'filetype' ) ) {
				options.push(
					`${ input.name }:${ input.value.split( /[,|\s]+/ ).join( '|' ) }`
				);
			} else if ( input.classList.contains( 'color' ) ) {
				options.push( `${ input.name }:#${ input.value }` );
			} else if ( 'class' === input.name ) {
				input.value.split( ' ' ).forEach( term => {
					options.push( `class:${ term }` );
				} );
			} else {
				options.push( `${ input.name }:${ input.value }` );
			}
		}
	} );

	if ( 'radio' === basetype ) {
		options.push( 'default:1' );
	}

	const type = basetype +
		( form.querySelector( '[name="required"]:checked' ) ? '*' : '' );

	const composed = type + ' ' + name + ' ' + options.join( ' ' );

	return `[${ composed.trim() }]`;
};


export {
	init
};
