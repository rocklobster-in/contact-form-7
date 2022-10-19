import { setStatus } from './status';


export default function validate( form, options = {} ) {
	const scope = form;

	if ( undefined === form.wpcf7?.schema ) {
		return;
	}

	const schema = { ...form.wpcf7.schema };

	// Event target is not a wpcf7 form control.
	if ( ! options.target?.closest( '.wpcf7-form-control-wrap[data-name]' ) ) {
		return;
	}

	if ( options.target?.closest( '.novalidate' ) ) {
		return;
	}

	const formData = new FormData();

	const targetFields = [];

	for ( const wrap of scope.querySelectorAll( '.wpcf7-form-control-wrap' ) ) {
		if ( wrap.closest( '.novalidate' ) ) {
			continue;
		}

		wrap.querySelectorAll(
			':where( input, textarea, select ):enabled'
		).forEach( control => {
			if ( ! control.name ) {
				return;
			}

			switch ( control.type ) {
				case 'button':
				case 'image':
				case 'reset':
				case 'submit':
					break;
				case 'checkbox':
				case 'radio':
					if ( control.checked ) {
						formData.append( control.name, control.value );
					}
					break;
				case 'select-multiple':
					for ( const option of control.selectedOptions ) {
						formData.append( control.name, option.value );
					}
					break;
				case 'file':
					for ( const file of control.files ) {
						formData.append( control.name, file );
					}
					break;
				default:
					formData.append( control.name, control.value );
			}
		} );

		if ( wrap.dataset.name ) {
			targetFields.push( wrap.dataset.name );

			wrap.setAttribute( 'data-under-validation', '1' );

			if (
				wrap.dataset.name === options.target.name.replace( /\[.*\]$/, '' )
			) {
				break;
			}
		}
	}

	schema.rules = ( schema.rules ?? [] ).filter(
		( { field } ) => targetFields.includes( field )
	);

	const prevStatus = form.getAttribute( 'data-status' );

	Promise.resolve( setStatus( form, 'validating' ) )
		.then( status => {
			if ( undefined !== swv ) {
				const result = swv.validate( schema, formData, options );

				for ( const [ field, { error } ] of result ) {
					removeValidationError( form, field );

					if ( undefined !== error ) {
						setValidationError( form, field, error );
					}
				}
			}
		} )
		.finally( () => {
			setStatus( form, prevStatus );

			form.querySelectorAll(
				'.wpcf7-form-control-wrap[data-under-validation]'
			).forEach( wrap => {
				wrap.removeAttribute( 'data-under-validation' );
			} );
		} );
}


export const setValidationError = ( form, fieldName, message ) => {
	const errorId = `${ form.wpcf7?.unitTag }-ve-${ fieldName }`
		.replaceAll( /[^0-9a-z_-]+/ig, '' );

	const firstFoundControl = form.querySelector(
		`.wpcf7-form-control-wrap[data-name="${ fieldName }"] .wpcf7-form-control`
	);

	const setScreenReaderValidationError = () => {
		const li = document.createElement( 'li' );

		li.setAttribute( 'id', errorId );

		if ( firstFoundControl && firstFoundControl.id ) {
			li.insertAdjacentHTML(
				'beforeend',
				`<a href="#${ firstFoundControl.id }">${ message }</a>`
			);
		} else {
			li.insertAdjacentText(
				'beforeend',
				message
			);
		}

		form.wpcf7.parent.querySelector(
			'.screen-reader-response ul'
		).appendChild( li );
	};

	const setVisualValidationError = () => {
		form.querySelectorAll(
			`.wpcf7-form-control-wrap[data-name="${ fieldName }"]`
		).forEach( wrap => {
			if (
				'validating' === form.getAttribute( 'data-status' ) &&
				! wrap.dataset.underValidation
			) {
				return;
			}

			const tip = document.createElement( 'span' );
			tip.classList.add( 'wpcf7-not-valid-tip' );
			tip.setAttribute( 'aria-hidden', 'true' );
			tip.insertAdjacentText( 'beforeend', message );
			wrap.appendChild( tip );

			wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
				elm.setAttribute( 'aria-invalid', 'true' );
			} );

			wrap.querySelectorAll( '.wpcf7-form-control' ).forEach( control => {
				control.classList.add( 'wpcf7-not-valid' );
				control.setAttribute( 'aria-describedby', errorId );

				if ( typeof control.setCustomValidity === 'function' ) {
					control.setCustomValidity( message );
				}

				if ( control.closest( '.use-floating-validation-tip' ) ) {
					control.addEventListener( 'focus', event => {
						tip.setAttribute( 'style', 'display: none' );
					} );

					tip.addEventListener( 'click', event => {
						tip.setAttribute( 'style', 'display: none' );
					} );
				}
			} );
		} );
	};

	setScreenReaderValidationError();
	setVisualValidationError();
};


export const removeValidationError = ( form, fieldName ) => {
	const errorId = `${ form.wpcf7?.unitTag }-ve-${ fieldName }`
		.replaceAll( /[^0-9a-z_-]+/ig, '' );

	form.wpcf7.parent.querySelector(
		`.screen-reader-response ul li#${ errorId }`
	)?.remove();

	form.querySelectorAll(
		`.wpcf7-form-control-wrap[data-name="${ fieldName }"]`
	).forEach( wrap => {
		wrap.querySelector( '.wpcf7-not-valid-tip' )?.remove();

		wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
			elm.setAttribute( 'aria-invalid', 'false' );
		} );

		wrap.querySelectorAll( '.wpcf7-form-control' ).forEach( control => {
			control.removeAttribute( 'aria-describedby' );
			control.classList.remove( 'wpcf7-not-valid' );

			if ( typeof control.setCustomValidity === 'function' ) {
				control.setCustomValidity( '' );
			}
		} );
	} );
};
