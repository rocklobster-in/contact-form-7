import {
	validators,
	validate,
	registerMiddleware as use
} from '@contactable/swv';

window.swv = {
	validators,
	validate,
	use,
	...( window.swv ?? {} ),
};
