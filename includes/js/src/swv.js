import * as validators from './swv/rules';
import FormDataTree from './swv/form-data-tree';

export default function validate( form, context ) {
	const rules = form.wpcf7.schema.rules ?? [];
	const validators = validate.validators ?? {};
	const formDataTree = new FormDataTree( form );

	rules.filter( ( { field, ...properties } ) => {
		return field === context.field;
	} ).forEach( ( { rule, ...properties } ) => {
		if ( 'function' === typeof validators[rule] ) {
			validators[rule].call( { rule, ...properties }, formDataTree );
		}
	} );
}

validate.validators = validators;
