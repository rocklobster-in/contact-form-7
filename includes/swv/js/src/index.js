import { validators, validate } from '@contactable/swv';

window.swv = {
	validators,
	validate,
	...( window.swv ?? {} ),
};
