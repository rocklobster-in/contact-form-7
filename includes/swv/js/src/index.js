import FormDataTree from '@rocklobsterinc/form-data-tree';

import {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rules as ruleDictionary,
} from '@rocklobsterinc/swv';


const constructRule = properties => {
	const { ruleName: rule, rules, ...remainingProperties } = properties;

	if ( ruleDictionary.has( ruleName ) ) {
		const constructor = ruleDictionary.get( ruleName );
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
	ruleDictionary,
	constructRule,
	...( window.swv ?? {} ),
};
