import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import {
	PanelBody,
	ExternalLink,
	ComboboxControl,
	TextControl,
} from '@wordpress/components';

import fetchContactForms from './fetch';
import { getContactFormEditorLink } from './helpers';

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
					<PanelBody title={ attributes.title }>
						<ExternalLink href={ getContactFormEditorLink( attributes ) }>
							{ __( 'Edit this contact form', 'contact-form-7' ) }
						</ExternalLink>
					</PanelBody>
				) }
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
