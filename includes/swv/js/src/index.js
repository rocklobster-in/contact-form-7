import FormDataTree from '@rocklobsterinc/form-data-tree';

import { flatten } from '@rocklobsterinc/functions';

import {
	CompositeRule,
	rulesDictionary,
	InvalidityException as Invalidity
} from '@rocklobsterinc/swv';

function Schema( properties ) {
	CompositeRule.call( this );

	properties.rules.forEach( rule => {
		const ruleObj = Schema.createRule( rule );

		if ( ruleObj ) {
			this.addRule( ruleObj );
		}
	} );
}

Schema.prototype = {

	*validate( formDataTree, context ) {
		const rules = this.rules.filter( rule => rule.matches( context ) );

		for ( const rule of rules ) {
			try {
				rule.validate( formDataTree, context );
			} catch ( error ) {
				if ( error instanceof Invalidity ) {
					yield { field: error.field, message: error.message };
				} else {
					throw error;
				}
			}
		}
	},

};

Object.setPrototypeOf( Schema.prototype, CompositeRule.prototype );

Schema.createRule = properties => {
	if ( rulesDictionary.has( properties.rule ) ) {
		const ruleConstructor = rulesDictionary.get( properties.rule );
		const ruleObj = new ruleConstructor( properties );

		if ( ruleObj instanceof CompositeRule ) {
			properties.rules.forEach( subRule => {
				const subRuleObj = Schema.createRule( subRule );

				if ( subRuleObj ) {
					ruleObj.addRule( subRuleObj );
				}
			} );
		}

		return ruleObj;
	}
};

const validate = ( schema, formData, context ) => {
	const schemaObj = new Schema( schema );
	const formDataTree = FormDataTree.from( formData );

	const result = {};

	for ( const { field, message } of schemaObj.validate( formDataTree ) ) {
		result[ field ] ??= { error: message };
	}

	for ( const field in formDataTree.trunk ) {
		if ( undefined === result[ field ] ) {
			const values = flatten( formDataTree.getAll( field ) );

			if ( values.length ) {
				result[ field ] = { validInputs: values };
			}
		}

		if ( undefined === result[ field ] ) {
			const files = flatten( formDataTree.getAllFiles( field ) );

			if ( files.length ) {
				result[ field ] = { validInputs: files };
			}
		}
	}

	return result;
};

window.swv = {
	rulesDictionary,
	validate,
};
