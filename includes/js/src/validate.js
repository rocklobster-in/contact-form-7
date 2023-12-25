import { setStatus } from './status';


export default function validate( form, options = {} ) {
	const {
		target,
		scope = form,
		...remainingOptions
	} = options;

	if ( undefined === form.wpcf7?.schema ) {
		return;
	}

	const schema = { ...form.wpcf7.schema };

	if ( undefined !== target ) {
		if ( ! form.contains( target ) ) {
			return;
		}

		// Event target is not a wpcf7 form control.
		if ( ! target.closest( '.wpcf7-form-control-wrap[data-name]' ) ) {
			return;
		}

		if ( target.closest( '.novalidate' ) ) {
			return;
		}
	}

	const wrapList = scope.querySelectorAll( '.wpcf7-form-control-wrap' );

	const formData = Array.from( wrapList ).reduce( ( formData, wrap ) => {
		if ( wrap.closest( '.novalidate' ) ) {
			return formData;
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

		return formData;
	}, new FormData() );

	const prevStatus = form.getAttribute( 'data-status' );

	Promise.resolve( setStatus( form, 'validating' ) )
		.then( status => {
			if ( undefined !== swv ) {
				const result = swv.validate( schema, formData, options );

				for ( const wrap of wrapList ) {
					if ( undefined === wrap.dataset.name ) {
						continue;
					}

					const field = wrap.dataset.name;

					if ( result.has( field ) ) {
						const { error, validInputs } = result.get( field );

						removeValidationError( form, field );

						if ( undefined !== error ) {
							setValidationError( form, field, error, { scope } );
						}

						updateReflection( form, field, validInputs ?? [] );
					}

					if ( wrap.contains( target ) ) {
						break;
					}
				}
			}
		} )
		.finally( () => {
			setStatus( form, prevStatus );
		} );
}


export const setValidationError = ( form, fieldName, message, options ) => {
	const {
		scope = form,
		...remainingOptions
	} = options ?? {};

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
		scope.querySelectorAll(
			`.wpcf7-form-control-wrap[data-name="${ fieldName }"]`
		).forEach( wrap => {
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


export const updateReflection = ( form, field, validInputs ) => {
	form.querySelectorAll(
		`[data-reflection-of="${ field }"]`
	).forEach( reflection => {
		if ( 'output' === reflection.tagName.toLowerCase() ) {
			const output = reflection;

			if ( 0 === validInputs.length ) {
				validInputs.push( output.dataset.default );
			}

			validInputs.slice( 0, 1 ).forEach( input => {
				if ( input instanceof File ) {
					input = input.name;
				}

				output.textContent = input;
			} );

		} else {
			reflection.querySelectorAll(
				'output'
			).forEach( output => {
				if ( output.hasAttribute( 'data-default' ) ) {
					if ( 0 === validInputs.length ) {
						output.removeAttribute( 'hidden' );
					} else {
						output.setAttribute( 'hidden', 'hidden' );
					}
				} else {
					output.remove();
				}
			} );

			validInputs.forEach( input => {

				if ( input instanceof File ) {
					input = input.name;
				}

				const output = document.createElement( 'output' );

				output.setAttribute( 'name', field );
				output.textContent = input;

				reflection.appendChild( output );
			} );
		}
	} );
};
