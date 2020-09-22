import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';

const contactForms = new Map();

apiFetch( {
	path: 'contact-form-7/v1/contact-forms',
} ).then( response => {
	Object.entries( response ).forEach( ( [ key, value ] ) => {
		contactForms.set( value.id, value );
	} );
} );

export default function ContactFormSelectorEdit( { attributes, setAttributes } ) {
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
}
