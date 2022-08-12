import * as validators from './rules';

window.swv = {
	validators,
	...( window.swv ?? {} ),
};
