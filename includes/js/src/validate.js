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

	removeValidationError( form, options.target );

	try {
		rules.forEach( ( { rule, ...properties } ) => {
			if ( 'function' === typeof validators[rule] ) {
				validators[rule].call( { rule, ...properties }, formDataTree );
			}
		} );
	} catch ( error ) {
		setValidationError( form, options.target, error.error );
	}
}

validate.validators = validators;


export const setValidationError = ( form, target, message ) => {
	const errorId = `${ form.wpcf7?.unitTag }-ve-${ target.name }`;

	const setScreenReaderValidationError = () => {
		const li = document.createElement( 'li' );

		li.setAttribute( 'id', errorId );

		if ( target.id ) {
			li.insertAdjacentHTML(
				'beforeend',
				`<a href="#${ target.id }">${ message }</a>`
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
		const wrap = form.querySelector(
			`span.wpcf7-form-control-wrap.${ target.name }`
		);

		const control = wrap.querySelector( '.wpcf7-form-control' );
		control.classList.add( 'wpcf7-not-valid' );
		control.setAttribute( 'aria-describedby', errorId );

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


export const removeValidationError = ( form, target ) => {
	const errorId = `${ form.wpcf7?.unitTag }-ve-${ target.name }`;

	form.wpcf7.parent.querySelector(
		`.screen-reader-response ul li#${ errorId }`
	)?.remove();

	const wrap = form.querySelector(
		`span.wpcf7-form-control-wrap.${ target.name }`
	);

	wrap.querySelector( '.wpcf7-not-valid-tip' )?.remove();

	wrap.querySelectorAll( '[aria-invalid]' ).forEach( elm => {
		elm.setAttribute( 'aria-invalid', 'false' );
	} );

	wrap.querySelectorAll( '.wpcf7-form-control' ).forEach( control => {
		control.removeAttribute( 'aria-describedby' );
		control.classList.remove( 'wpcf7-not-valid' );
	} );
};
