import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import icon from './icon';
import edit from './edit';
import transforms from './transforms';

registerBlockType( 'contact-form-7/contact-form-selector', {

	title: __( 'Contact Form 7', 'contact-form-7' ),

	description: __( "Insert a contact form you have created with Contact Form 7.", 'contact-form-7' ),

	category: 'widgets',

	attributes: {
		id: {
			type: 'integer',
		},
		title: {
			type: 'string',
		},
	},

	icon,

	transforms,

	edit,

	save: ( { attributes } ) => {
		return(
			<div>
				[contact-form-7 id="{ attributes.id }" title="{ attributes.title }"]
			</div>
		);
	},
} );
