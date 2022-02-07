export default function validate( form ) {
	const rules = form.wpcf7.schema.rules ?? [];

	rules.forEach( ( { rule, field, message, ...properties } ) => {
		console.log( message );
	} );
}
