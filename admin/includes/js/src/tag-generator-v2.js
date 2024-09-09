const init = form => {
	form.reset() ?? update( form );

	form.querySelectorAll(
		'.control-box'
	).forEach( box => {
		box.addEventListener( 'change', event => {
			update( form );
		} );

		box.addEventListener( 'keyup', event => {
			if (
				'text' === ( event.target.type ?? '' ) ||
				'textarea' === event.target.tagName?.toLowerCase()
			) {
				update( form );
			}
		} );
	} );
};


const update = form => {
	const composed = compose( form );

	if ( composed ) {
		form.querySelectorAll(
			'[data-tag-part="tag"]'
		).forEach( tag => {
			tag.value = composed;
		} );

		form.querySelectorAll(
			'[data-taggen="insert-tag"]'
		).forEach( tag => {
			tag.disabled = false;
		} );

		form.querySelectorAll(
			'[data-tag-part="mail-tag"]'
		).forEach( tag => {
			const nameField = form.querySelector( '[data-tag-part="name"]' );

			if ( nameField ) {
				tag.innerText = `[${ nameField.value.trim() }]`;
			}
		} );
	} else {
		form.querySelectorAll(
			'[data-tag-part="tag"]'
		).forEach( tag => {
			tag.value = '';
		} );

		form.querySelectorAll(
			'[data-taggen="insert-tag"]'
		).forEach( tag => {
			tag.disabled = true;
		} );
	}
};


const compose = scope => {
	const basetype = scope.querySelector(
		'[data-tag-part="basetype"]'
	)?.value.trim();

	if ( ! basetype ) {
		return;
	}

	if ( scope.querySelector( ':invalid' ) ) {
		return;
	}

	let type = basetype;

	const typeSuffixField = scope.querySelector(
		'[data-tag-part="type-suffix"]'
	);

	if ( typeSuffixField ) {
		if ( [ 'checkbox', 'radio' ].includes( typeSuffixField?.type ) ) {
			type += typeSuffixField.checked ? typeSuffixField.value.trim() : '';
		} else {
			type += typeSuffixField.value.trim();
		}
	}

	const nameField = scope.querySelector( '[data-tag-part="name"]' );

	let name = nameField?.value.trim() ?? '';

	if ( nameField ) {
		name ||= `${ basetype }-${ Math.floor( Math.random() * 1000 ) }`;
		nameField.value = name;
	}

	const options = [];

	scope.querySelectorAll(
		'[data-tag-part="option"]'
	).forEach( field => {
		const tagOption = field.dataset.tagOption?.trim();

		if ( ! tagOption ) {
			return;
		}

		if ( [ 'checkbox', 'radio' ].includes( field?.type ) && ! field.checked ) {
			return;
		}

		tagOption.split( ' ' ).forEach( opt => {
			if ( opt.endsWith( ':' ) ) {
				field.value?.split( ' ' ).forEach( val => {
					if ( val = val.trim() ) {
						options.push( opt + val );
					}
				} );
			} else {
				options.push( opt );
			}
		} );
	} );

	const values = scope.querySelector( '[data-tag-part="value"]' )?.value
		.split( "\n" ).map( line => line.trim() ).filter( line => '' !== line )
		.map( line => `"${ line.replace( /["]/g, '&quot;' ) }"` ) ?? [];

	const composed = [
		type,
		name,
		options.join( ' ' ),
		values.join( ' ' ),
	].map( item => item.trim() ).filter( item => '' !== item );

	const content = scope.querySelector(
		'[data-tag-part="content"]'
	)?.value.trim();

	if ( content ) {
		return `[${ composed.join( ' ' ) }] ${ content } [/${ type }]`;
	} else {
		return `[${ composed.join( ' ' ) }]`;
	}
};


export {
	init
};
