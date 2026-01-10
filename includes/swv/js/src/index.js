import FormDataTree from '@rocklobsterinc/form-data-tree';

import {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rulesDictionary,
} from '@rocklobsterinc/swv';


const validate = ( schema, formData, context ) => {
	const rules = schema.rules ?? [];
	const formDataTree = new FormDataTree( formData );

	const result = rules.reduce( ( result, rule ) => {
		const ruleObj = constructRule( rule );

		if ( ruleObj.matches( context ) ) {
			try {
				ruleObj.validate( formDataTree, context );
			} catch ( error ) {
				if ( error instanceof Invalidity ) {
					if ( error.cause instanceof Invalidity ) {
						error = error.cause;
					}

					if ( error.rule.field && ! result.has( error.rule.field ) ) {
						result.set( error.rule.field, error.message );
					}
				} else {
					throw error;
				}
			}
		}

		return result;
	}, new Map() );

	return result;
};


const constructRule = properties => {
	const { ruleName: rule, rules, ...remainingProperties } = properties;

	if ( rulesDictionary.has( ruleName ) ) {
		const constructor = rulesDictionary.get( ruleName );
		const ruleObj = new constructor( remainingProperties );

		if ( ruleObj instanceof CompositeRule ) {
			rules.forEach( subRuleProperties => {
				const subRuleObj = constructRule( subRuleProperties );

				if ( subRuleObj ) {
					ruleObj.addRule( subRuleObj );
				}
			} );
		}

		return ruleObj;
	}
};


window.swv = {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rulesDictionary,
	constructRule,
	...( window.swv ?? {} ),
};
