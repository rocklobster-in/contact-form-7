import { ValidationError } from './error';

function getFieldValues( formData, fieldName ) {
	let values = [];

	for ( const field of formData.entries() ) {
		const name = field[ 0 ];
		const value = field[ 1 ];

		if ( fieldName === name && '' !== value ) {
			values.push( value );
		}
	}

	return values;
}

export const required = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	if ( 0 === values.length ) {
		throw new ValidationError( 'invalid_required', this.message );
	}
};
