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


const normalize = input => {
	if ( 'name' === input.name ) {
		input.value = input.value
			.replace( /[^0-9a-zA-Z:._-]/g, '' )
			.replace( /^[^a-zA-Z]+/, '' );
	}

	if ( input.classList.contains( 'numeric' ) ) {
		input.value = input.value.replace( /[^0-9.-]/g, '' );
	}

	if ( input.classList.contains( 'idvalue' ) ) {
		input.value = input.value.replace( /[^-0-9a-zA-Z_]/g, '' );
	}

	if ( input.classList.contains( 'classvalue' ) ) {
		input.value = input.value.split( ' ' )
			.map( term => term.replace( /[^-0-9a-zA-Z_]/g, '' ) )
			.join( ' ' ).replace( /\s+/g, ' ' ).trim();
	}

	if ( input.classList.contains( 'color' ) ) {
		input.value = input.value.replace( /[^0-9a-fA-F]/g, '' );
	}

	if ( input.classList.contains( 'filesize' ) ) {
		input.value = input.value.replace( /[^0-9kKmMbB]/g, '' );
	}

	if ( input.classList.contains( 'filetype' ) ) {
		input.value = input.value.replace( /[^0-9a-zA-Z.,|\s]/g, '' );
	}

	if ( input.classList.contains( 'date' ) ) {
		if ( ! input.value.match( /^\d{4}-\d{2}-\d{2}$/ ) ) {
			input.value = '';
		}
	}

	if ( 'values' === input.name ) {
		input.value = input.value.trim();
	}
};


const compose = ( basetype, form ) => {
	const scope = form.querySelector( `.scope.${ basetype }` ) ?? form;

	const type = basetype +
		( form.querySelector( '[name="required"]:checked' ) ? '*' : '' );

	const name = form.querySelector( '[name="name"]' )?.value ?? '';

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

	const values = scope.querySelector( '[name="values"]' )?.value.split( "\n" )
		.map( line => line.trim() ).filter( line => '' !== line )
		.map( line => `"${ line.replace( /["]/g, '&quot;' ) }"` ) ?? [];

	const composed = [
		type,
		name,
		options.join( ' ' ),
		values.join( ' ' ),
	].map( item => item.trim() ).filter( item => '' !== item );

	const content = scope.querySelector( '[name="content"]' )?.value.trim();

	if ( content ) {
		return `[${ composed.join( ' ' ) }] ${ content } [/${ type }]`;
	} else {
		return `[${ composed.join( ' ' ) }]`;
	}
};


export {
	init
};
