import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	PanelBody,
	ComboboxControl,
	TextControl,
	ToggleControl
} from '@wordpress/components';

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

	const [ filteredOptions, setFilteredOptions ] = useState( options );

	return(
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Shortcode attributes', 'contact-form-7' ) }>
					<TextControl
						label={ __( 'ID', 'contact-form-7' ) }
						value={ attributes.htmlId }
						onChange={
							( value ) => setAttributes( {
								htmlId: value
							} )
						}
					/>
					<TextControl
						label={ __( 'Name', 'contact-form-7' ) }
						value={ attributes.htmlName }
						onChange={
							( value ) => setAttributes( {
								htmlName: value
							} )
						}
					/>
					<TextControl
						label={ __( 'Title', 'contact-form-7' ) }
						value={ attributes.htmlTitle }
						onChange={
							( value ) => setAttributes( {
								htmlTitle: value
							} )
						}
					/>
					<TextControl
						label={ __( 'Class', 'contact-form-7' ) }
						value={ attributes.htmlClass }
						onChange={
							( value ) => setAttributes( {
								htmlClass: value
							} )
						}
					/>
					<ToggleControl
						label={ __( 'Output the raw form template', 'contact-form-7' ) }
						checked={ 'raw_form' === attributes.output }
						onChange={
							( state ) => setAttributes( {
								output: state ? 'raw_form' : 'form'
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'components-placeholder' } ) }>
				<ComboboxControl
					label={ __( "Select a contact form:", 'contact-form-7' ) }
					options={ filteredOptions }
					value={ attributes.id }
					onChange={
						( value ) => setAttributes( {
							id: parseInt( value ),
							title: contactForms.get( parseInt( value ) ).title
						} )
					}
					onFilterValueChange={
						( inputValue ) => setFilteredOptions(
							options.filter( ( option ) =>
								option.label.toLowerCase()
									.startsWith( inputValue.toLowerCase() )
							)
						)
					}
				/>
			</div>
		</>
	);
}
