import apiFetch from '@wordpress/api-fetch';

import {
	iconInCircle,
 	canonicalizeName,
} from './utils';


const init = () => {
	document.querySelectorAll(
		'#contact-form-editor [data-config-field]'
	).forEach( field => {
		field.addEventListener( 'change', event => {
			const id = document.querySelector( '[name="post_ID"]' )?.value;

			if ( id && 0 < id ) {
				verifyContactForm( id );
			}
		} );
	} );

	update();
};


const update = () => {
	document.querySelectorAll(
		'#contact-form-editor .config-error'
	).forEach( error => {
		error.remove();
	} );

	document.querySelectorAll(
		'#contact-form-editor [data-config-field]'
	).forEach( field => {
		const section = field.dataset.configField;

		if ( countErrors( { section } ) ) {
			field.setAttribute( 'aria-invalid', 'true' );

			field.setAttribute( 'aria-describedby',
				canonicalizeName( `wpcf7-config-error-for-${ section }` )
			);

			field.after( createErrorsList( section ) );
		} else { // Valid field
			field.removeAttribute( 'aria-invalid' );
		}
	} );
};


const retrieveErrors = args => {
	const {
		section,
		tab,
	} = args;

	const errors = [];

	for ( const prop in wpcf7.configValidator.errors ) {
		if ( prop === section ) {
			errors.push( ...wpcf7.configValidator.errors[ prop ] );
		}
	}

	return errors;
};


const countErrors = args => {
	const errors = retrieveErrors( args );
	return errors.length;
};


const createErrorsList = section => {
	if ( ! section ) {
		return '';
	}

	const ul = document.createElement( 'ul' );

	ul.setAttribute( 'id',
		canonicalizeName( `wpcf7-config-error-for-${ section }` )
	);

	ul.classList.add( 'config-error' );

	const errors = retrieveErrors( { section } );

	errors.forEach( err => {
		if ( ! err.message ) {
			return;
		}

		const li = document.createElement( 'li' );
		li.append( iconInCircle( '!' ) );

		if ( err.link ) {
			const anchor = document.createElement( 'a' );
			anchor.setAttribute( 'href', err.link );
			anchor.append( err.message );
			li.append( ' ', anchor );
		} else {
			li.append( ' ', err.message );
		}

		ul.append( li );
	} );

	return ul;
};


const verifyContactForm = id => {
	const {
		namespace,
	} = wpcf7.apiSettings;

	const path = `/${ namespace }/contact-forms/${ id }`;

	const data = new FormData();

	document.querySelectorAll(
		'#contact-form-editor [data-config-field]'
	).forEach( field => {
		const name = field.name?.replace( /^wpcf7-/, '' ).replace( /-/g, '_' );

		if ( ! name ) {
			return;
		}

		let value;

		if ( [ 'checkbox', 'radio' ].includes( field.type ) ) {
			if ( field.checked ) {
				value = field.value;
			}
		} else {
			value = field.value;
		}

		if ( value === undefined ) {
			return;
		}

		if ( name.endsWith( '[]' ) ) {
			data.append( name, value );
		} else {
			data.set( name, value );
		}
	} );

	data.set( 'context', 'dry-run' );

	apiFetch( {
		path,
		method: 'POST',
		body: data,
	} ).then( response => {
		wpcf7.configValidator.errors = response.config_errors;
		update();
	} );
};


export {
	init,
};
