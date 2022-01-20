import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
import { SelectControl } from '@wordpress/components';

export default function ContactFormSelectorEdit( { attributes, setAttributes } ) {
	const contactForms = new Map();

	Object.entries( window.wpcf7.contactForms ).forEach( ( [ key, value ] ) => {
		contactForms.set( value.id, value );
	} );

	if ( ! contactForms.size && ! attributes.id ) {
		return(
			<div className="components-placeholder">
				<p>
					{ __( "No contact forms were found. Create a contact form first.", 'contact-form-7' ) }
				</p>
			</div>
		);
	}

	const options = Array.from( contactForms.values(), ( val ) => {
		return { value: val.id, label: val.title };
	} );

	if ( ! attributes.id ) {
		const firstOption = options[0];

		attributes = {
			id: parseInt( firstOption.value ),
			title: firstOption.label,
		};
	} else if ( ! options.length ) {
		options.push( {
			value: attributes.id,
			label: attributes.title,
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
