import { validators, validate } from '@takayukister/swv';

window.swv = {
	validators,
	validate,
	...( window.swv ?? {} ),
};
