import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

window.wpcf7 = window.wpcf7 ?? {
	contactForms: [],
};

import icon from './icon';
import edit from './edit';
import transforms from './transforms';

registerBlockType( 'contact-form-7/contact-form-selector', {
	icon,

	transforms,

	edit,

	save: ( { attributes } ) => {

		attributes = {
			id: attributes.id ?? window.wpcf7.contactForms[ 0 ]?.id,
			title: attributes.title ?? window.wpcf7.contactForms[ 0 ]?.title,
		};

		return(
			<div { ...useBlockProps.save() }>
				[contact-form-7 id="{ attributes.id }" title="{ attributes.title }"]
			</div>
		);
	},
} );
