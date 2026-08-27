import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import icon from './icon.js';
import edit from './edit.js';
import transforms from './transforms.js';
import { createShortcode } from './helpers.js';

registerBlockType( 'contact-form-7/contact-form-selector', {
	icon,

	transforms,

	edit,

	save: ( { attributes } ) => {
		const shortcode = createShortcode( attributes );

		return(
			<div { ...useBlockProps.save() }>
				{ shortcode }
			</div>
		);
	},
} );
