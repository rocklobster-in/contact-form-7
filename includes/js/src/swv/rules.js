import { getFieldValues } from './utils';
import { ValidationError } from './error';

export const required = function ( formData ) {
	const values = getFieldValues( formData, this.field );

	if ( 0 === values.length ) {
		throw new ValidationError( this );
	}
};
