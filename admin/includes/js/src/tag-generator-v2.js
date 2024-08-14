const init = form => {
	update( form );

	form.querySelectorAll(
		'.control-box'
	).forEach( box => {
		box.addEventListener( 'change', event => {
			update( form );
		} );
	} );
};


const update = form => {
	form.querySelectorAll(
		'[data-tag-part="tag"]'
	).forEach( tag => {
		tag.value = compose( form );
	} );
};


const compose = scope => {
	const basetype = scope.querySelector(
		'[data-tag-part="basetype"]'
	)?.value.trim();

	if ( ! basetype ) {
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

		if ( 'class:' === tagOption ) {
			field.value?.split( ' ' ).forEach( classTerm => {
				classTerm = classTerm.trim();

				if ( classTerm ) {
					options.push( tagOption + classTerm );
				}
			} );
		} else if ( tagOption.endsWith( ':' ) ) {
			let val = field.value?.trim();

			if ( val ) {
				options.push( tagOption + val );
			}
		} else {
			options.push( tagOption );
		}
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

	return `[${ composed.join( ' ' ) }]`;
};


export {
	init
};
