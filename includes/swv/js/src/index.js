import FormDataTree from '@rocklobsterinc/form-data-tree';

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

window.swv = {
	Schema,
	rulesDictionary,
};
