import apiFetch from '@wordpress/api-fetch';

export default function submit( form ) {
	if ( typeof window.FormData !== 'function' ) {
		return;
	}

	const formData = new FormData( form );

	const detail = {
		id: form.wpcf7.unitTag,
		unitTag: form.wpcf7.unitTag,
		contactFormId: form.wpcf7.id,
		pluginVersion: form.wpcf7.pluginVersion,
		contactFormLocale: form.wpcf7.locale,
		containerPostId: form.wpcf7.containerPost,
		status: form.wpcf7.status,
		inputs: Array.from(
			formData,
			val => {
				const name = val[0], value = val[1];
				return name.match( /^_/ ) ? false : { name, value };
			}
		).filter( val => false !== val ),
		formData,
	};

	apiFetch( {
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/feedback`,
		method: 'POST',
		body: formData,
	} ).then( response => {
		detail.id = document.querySelector( response.into ).getAttribute( 'id' );
		detail.status = response.status;
		detail.apiResponse = response;

		switch ( response.status ) {
			case 'init':
				wpcf7.setStatus( form, 'init' );
				break;

			case 'validation_failed':
				wpcf7.setStatus( form, 'invalid' );
				break;

			case 'acceptance_missing':
				wpcf7.setStatus( form, 'unaccepted' );
				break;

			case 'spam':
				wpcf7.setStatus( form, 'spam' );
				break;

			case 'aborted':
				wpcf7.setStatus( form, 'aborted' );
				break;

			case 'mail_sent':
				wpcf7.setStatus( form, 'sent' );
				break;

			case 'mail_failed':
				wpcf7.setStatus( form, 'failed' );
				break;

			default:
				wpcf7.setStatus( form,
					`custom-${ response.status.replace( /[^0-9a-z]+/i, '-' ) }`
				);
		}
	} );
}
