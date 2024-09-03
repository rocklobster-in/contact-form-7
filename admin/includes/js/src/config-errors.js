import { iconInCircle } from './utils';


const init = () => {
	document.querySelectorAll(
		'#contact-form-editor [data-config-field]'
	).forEach( field => {
		field.addEventListener( 'change', event => {
			// Todo: Update
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
		const errors = retrieveErrors( section );

		if ( errors ) {
			field.setAttribute( 'aria-invalid', 'true' );

			field.after( createErrorsList( {
				section,
				errors,
			} ) );
		} else { // Valid field
			field.removeAttribute( 'aria-invalid' );
		}
	} );
};


const retrieveErrors = section => {
	const configErrors = wpcf7.configValidator.errors;
	return configErrors[ section ];
};


const createErrorsList = args => {
	const {
		section,
		errors = [],
	} = args;

	if ( ! section ) {
		return '';
	}

	const ul = document.createElement( 'ul' );
	ul.setAttribute( 'id', `wpcf7-config-error-for-${ section }` );
	ul.setAttribute( 'class', 'config-error' );

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


const countErrors = ( tab = '' ) => {
	const configErrors = wpcf7.configValidator.errors;

	if ( tab ) {
		tab = tab.replace( /-panel$/, '' );
		return configErrors[ tab ]?.length;
	} else {
		return configErrors.length;
	}
};


export {
	init,
};
