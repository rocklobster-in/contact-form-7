import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { SelectControl } from '@wordpress/components';

const contactForms = new Map();

registerBlockType( 'contact-form-7/contact-form-selector', {

	title: __( 'Contact Form 7', 'contact-form-7' ),

	description: __( "Insert a contact form you have created with Contact Form 7.", 'contact-form-7' ),

	icon: 'email',

	category: 'widgets',

	attributes: {
		id: {
			type: 'integer',
		},
		title: {
			type: 'string',
		},
	},

	edit: ( { attributes, setAttributes } ) => {
		const options = Array.from( contactForms.values(), ( val ) => { 
			return { value: val.id, label: val.title };
		} );

		if ( ! attributes.id ) {
			const firstOption = options[0];

			setAttributes( {
				id: parseInt( firstOption.value ),
				title: firstOption.label,
			} );
		}

		return(
			<SelectControl
				options={ options }
				value={ attributes.id }
				onChange={
					( value ) => setAttributes( {
						id: parseInt( value ),
						title: contactForms.get( parseInt( value ) ).title
					} )
				}
			/>
		);
	},

	save: ( { attributes } ) => {
		return(
			<div>
				[contact-form-7 id="{ attributes.id }" title="{ attributes.title }"]
			</div>
		);
	},
} );

apiFetch( {
	path: 'contact-form-7/v1/contact-forms',
} ).then( response => {
	Object.entries( response ).forEach( ( [ key, value ] ) => {
		contactForms.set( value.id, value );
	} );
} );
