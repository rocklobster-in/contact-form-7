import {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rules as availableRules,
} from '@rocklobsterinc/swv';


function WPCF7Schema( properties ) {
	for ( const rule of properties.rules ) {
		if ( availableRules.has( rule.rule ) ) {
			const Constructor = availableRules.get( rule.rule );
			this.addRule( new Constructor( rule ) );
		}
	}
}

Object.setPrototypeOf( WPCF7Schema.prototype, CompositeRule.prototype );


window.swv = {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	availableRules,
	WPCF7Schema,
	...( window.swv ?? {} ),
};
