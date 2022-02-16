import * as validators from './swv/rules';

export default function validate( form, context ) {
	const rules = form.wpcf7.schema.rules ?? [];
	const formData = new FormData( form );

	rules.filter( ( { field, ...properties } ) => {
		return field === context.field;
	} ).forEach( ( { rule, ...properties } ) => {
		if ( 'function' === typeof validators[rule] ) {
			validators[rule].call( { rule, ...properties }, formData );
		}
	} );
}
