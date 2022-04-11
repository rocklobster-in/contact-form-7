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
	if ( undefined === target.name || '' === target.name ) {
		return;
	}

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
		form.querySelectorAll(
			`.wpcf7-form-control-wrap.${ target.name }`
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


export const removeValidationError = ( form, target ) => {
	if ( undefined === target.name || '' === target.name ) {
		return;
	}

	const errorId = `${ form.wpcf7?.unitTag }-ve-${ target.name }`;

	form.wpcf7.parent.querySelector(
		`.screen-reader-response ul li#${ errorId }`
	)?.remove();

	form.querySelectorAll(
		`span.wpcf7-form-control-wrap.${ target.name }`
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
