import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	PanelBody,
	ComboboxControl,
	TextControl,
	ToggleControl
} from '@wordpress/components';

import fetchContactForms from './fetch';

export default function ContactFormSelectorEdit( { attributes, setAttributes } ) {
	const createMap = array => {
		return array.reduce(
			( map, current ) => map.set( current.id, current ),
			new Map()
		);
	};

	const convertToOptions = map => {
		const options = [];

		for ( const [ key, value ] of map ) {
			options.push( { value: key, label: value.title } );
		}

		return options;
	};

	const [ contactForms, setContactForms ] = useState(
		() => createMap( window.wpcf7.contactForms ?? [] )
	);

	if ( ! contactForms.size ) {
		return(
			<div className="components-placeholder">
				<p>
					{ __( "No contact forms were found. Create a contact form first.", 'contact-form-7' ) }
				</p>
			</div>
		);
	}

	const blockProps = {
		className: 'components-placeholder',
		style: {
			marginTop: '28px',
			marginBottom: '28px',
		},
	};

	return(
		<>
			<InspectorControls>
				{ attributes.id && (
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
				) }
			</InspectorControls>
			<div { ...useBlockProps( blockProps ) }>
				<ComboboxControl
					label={ __( "Select a contact form:", 'contact-form-7' ) }
					options={ convertToOptions( contactForms ) }
					value={ attributes.id }
					onChange={
						( value ) => setAttributes( {
							id: parseInt( value ),
							title: contactForms.get( parseInt( value ) )?.title
						} )
					}
					onFilterValueChange={
						( inputValue ) => {
							fetchContactForms( {
								search: inputValue
							} ).then( response => {
								setContactForms( createMap( response ) );
							} );
						}
					}
				/>
			</div>
		</>
	);
}
