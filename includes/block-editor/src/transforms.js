import { createBlock } from '@wordpress/blocks';

import { createShortcode } from './helpers';

const transforms = {
	from: [
		{
			type: 'shortcode',
			tag: 'contact-form-7',
			attributes: {
				id: {
					type: 'integer',
					shortcode: ( { named: { id } } ) => {
						return parseInt( id );
					},
				},
				title: {
					type: 'string',
					shortcode: ( { named: { title } } ) => {
						return title;
					},
				},
			},
		},
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
