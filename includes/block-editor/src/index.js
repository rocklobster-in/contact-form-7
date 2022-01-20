import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

window.wpcf7 = window.wpcf7 ?? {};

apiFetch( {
	path: 'contact-form-7/v1/contact-forms?per_page=20',
} ).then( response => {
	window.wpcf7.contactForms = response;
} );

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

		attributes = {
			id: attributes.id ?? window.wpcf7.contactForms[ 0 ]?.id,
			title: attributes.title ?? window.wpcf7.contactForms[ 0 ]?.title,
		};

		return(
			<div>
				[contact-form-7 id="{ attributes.id }" title="{ attributes.title }"]
			</div>
		);
	},
} );
