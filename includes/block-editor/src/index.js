import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

import icon from './icon';
import edit from './edit';
import transforms from './transforms';
import { createShortcode } from './helpers';

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
