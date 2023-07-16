import { createBlock } from '@wordpress/blocks';

import { createShortcode } from './helpers';

const transforms = {
	from: [
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/shortcode' ],
			transform: ( attributes ) => {
				const shortcode = createShortcode( attributes );

				return createBlock(
					'core/shortcode',
					{
						text: shortcode,
					}
				);
			},
		},
	],
};

export default transforms;
