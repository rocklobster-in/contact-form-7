import defaultConfig from '@wordpress/scripts/config/webpack.config.js';

export default {
 	...defaultConfig,
	output: {
		...defaultConfig.output,
		clean: false,
	},
};
