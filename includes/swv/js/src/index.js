import FormDataTree from '@takayukister/form-data-tree';

import * as validators from './rules';
import { ValidationError } from './error';

const validate = ( rules, formData ) => {
	const formDataTree = new FormDataTree( formData );

	const result = rules.reduce( ( prev, current ) => {
		const { rule, ...properties } = current;

		if ( prev.get( properties.field )?.error ) {
			return prev;
		}

		try {
			validators[rule].call( { rule, ...properties }, formDataTree );
		} catch ( error ) {
			if ( error instanceof ValidationError ) {
				return prev.set( properties.field, { error: error.error } );
			}
		}

		return prev.set( properties.field, {} );
	}, new Map() );

	return result;
};

window.swv = {
	validators,
	validate,
	...( window.swv ?? {} ),
};
