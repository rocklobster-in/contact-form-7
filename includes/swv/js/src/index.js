import FormDataTree from '@takayukister/form-data-tree';

import * as validators from './rules';
import { ValidationError } from './error';

const validate = ( schema, formData, options = {} ) => {
	const { targetFields } = options;

	const rules = ( schema.rules ?? [] ).filter(
		( { rule, ...properties } ) => {
			if ( 'function' !== typeof validators[ rule ] ) {
				return false;
			}

			if ( 'function' === typeof validators[ rule ].matches ) {
				return validators[ rule ].matches( properties, options );
			}

			if ( Array.isArray( targetFields ) && targetFields.length ) {
				return targetFields.includes( properties.field );
			}

			return true;
		}
	);

	if ( ! rules.length ) { // There is no rule to validate.
		return new Map();
	}

	const formDataTree = new FormDataTree( formData );

	const result = rules.reduce( ( prev, current ) => {
		const { rule, ...properties } = current;

		if ( prev.get( properties.field )?.error ) {
			return prev;
		}

		try {
			validators[ rule ].call( { rule, ...properties }, formDataTree );
		} catch ( error ) {
			if ( error instanceof ValidationError ) {
				return prev.set( properties.field, error );
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
