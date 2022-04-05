import * as validators from './swv/rules';
import FormDataTree from './swv/form-data-tree';


export default function validate( form, options = {} ) {
	const rules = ( form.wpcf7.schema.rules ?? [] ).filter(
		rule => rule.field === options.target?.name
	);

	if ( ! rules.length ) {
		return;
	}

	const validators = validate.validators ?? {};
	const formDataTree = new FormDataTree( form );

	try {
		rules.forEach( ( { rule, ...properties } ) => {
			if ( 'function' === typeof validators[rule] ) {
				validators[rule].call( { rule, ...properties }, formDataTree );
			}
		} );
	} catch ( error ) {
		setValidationError( form, {
			error_id: `${ form.wpcf7?.unitTag }-ve-${ options.target?.name }`,
			into: `span.wpcf7-form-control-wrap.${ options.target?.name }`,
			message: error.error,
			idref: options.target?.id,
		} );
	}
}

validate.validators = validators;


export const setValidationError = ( form, error ) => {
	const {
		error_id,
		into,
		message,
		idref,
	} = error;

	const setScreenReaderValidationError = () => {
		const li = document.createElement( 'li' );

		li.setAttribute( 'id', error_id );

		if ( idref ) {
			li.insertAdjacentHTML(
				'beforeend',
				`<a href="#${ idref }">${ message }</a>`
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
		const wrap = form.querySelector( into );

		const control = wrap.querySelector( '.wpcf7-form-control' );
		control.classList.add( 'wpcf7-not-valid' );
		control.setAttribute( 'aria-describedby', error_id );

		const tip = document.createElement( 'span' );
		tip.classList.add( 'wpcf7-not-valid-tip' );
		tip.setAttribute( 'aria-hidden', 'true' );
		tip.insertAdjacentText( 'beforeend', message );
		wrap.appendChild( tip );

		wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
			elm.setAttribute( 'aria-invalid', 'true' );
		} );

		if ( control.closest( '.use-floating-validation-tip' ) ) {
			control.addEventListener( 'focus', event => {
				tip.setAttribute( 'style', 'display: none' );
			} );

			tip.addEventListener( 'mouseover', event => {
				tip.setAttribute( 'style', 'display: none' );
			} );
		}
	};

	setScreenReaderValidationError();
	setVisualValidationError();
};
