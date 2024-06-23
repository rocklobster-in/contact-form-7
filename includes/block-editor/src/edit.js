import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
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
		() => createMap( [] )
	);

	useEffect( () => {
		fetchContactForms().then( response => {
			setContactForms( createMap( response ) );
		} );
	}, [] );

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
					<PanelBody
						title={ __( 'Form attributes', 'contact-form-7' ) }
						initialOpen={ false }
					>
						<TextControl
							label={ __( 'ID', 'contact-form-7' ) }
							value={ attributes.htmlId }
							onChange={
								( value ) => setAttributes( {
									htmlId: value
								} )
							}
							help={
								__( 'Used for the id attribute value of the form element.', 'contact-form-7' )
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
							help={
								__( 'Used for the name attribute value of the form element.', 'contact-form-7' )
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
							help={
								__( 'Used for the aria-label attribute value of the form element.', 'contact-form-7' )
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
							help={
								__( 'Used for the class attribute value of the form element.', 'contact-form-7' )
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
							hash: contactForms.get( parseInt( value ) )?.hash,
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
