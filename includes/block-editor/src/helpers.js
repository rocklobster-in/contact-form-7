import { addQueryArgs } from '@wordpress/url';


export const createShortcode = attributes => {
	let shortcode = `[contact-form-7]`;

	if ( attributes.hash ) {
		shortcode = shortcode.replace( /\]$/,
			` id="${ attributes.hash }"]`
		);
	} else if ( attributes.id ) {
		shortcode = shortcode.replace( /\]$/,
			` id="${ attributes.id }"]`
		);
	}

	if ( attributes.title ) {
		shortcode = shortcode.replace( /\]$/,
			` title="${ attributes.title }"]`
		);
	}

	if ( attributes.htmlId ) {
		shortcode = shortcode.replace( /\]$/,
			` html_id="${ attributes.htmlId }"]`
		);
	}

	if ( attributes.htmlName ) {
		shortcode = shortcode.replace( /\]$/,
			` html_name="${ attributes.htmlName }"]`
		);
	}

	if ( attributes.htmlTitle ) {
		shortcode = shortcode.replace( /\]$/,
			` html_title="${ attributes.htmlTitle }"]`
		);
	}

	if ( attributes.htmlClass ) {
		shortcode = shortcode.replace( /\]$/,
			` html_class="${ attributes.htmlClass }"]`
		);
	}

	if ( 'raw_form' === attributes.output ) {
		shortcode = shortcode.replace( /\]$/,
			` output="${ attributes.output }"]`
		);
	}

	return shortcode;
};


export const getContactFormEditorLink = attributes => {
	const adminRoot = ajaxurl.replace( /\/admin-ajax\.php$/, '/admin.php' );

	return addQueryArgs( adminRoot, {
		page: 'wpcf7',
		post: attributes.id,
		action: 'edit',
	} );
};
