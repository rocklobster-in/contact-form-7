import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
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

	const instanceId = useInstanceId( ContactFormSelectorEdit );
	const id = `contact-form-7-contact-form-selector-${ instanceId }`;

	return(
		<div className="components-placeholder">
			<label
				htmlFor={ id }
				className="components-placeholder__label"
			>
				{ __( "Select a contact form:", 'contact-form-7' ) }
			</label>
			<SelectControl
				id={ id }
				options={ options }
				value={ attributes.id }
				onChange={
					( value ) => setAttributes( {
						id: parseInt( value ),
						title: contactForms.get( parseInt( value ) ).title
					} )
				}
			/>
		</div>
	);
}
