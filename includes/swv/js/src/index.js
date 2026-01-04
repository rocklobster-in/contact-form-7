import {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	rules,
} from '@rocklobsterinc/swv';

window.swv = {
	InvalidityException,
	AbstractRule,
	CompositeRule,
	availableRules: rules,
	...( window.swv ?? {} ),
};
