import apiFetch from '@wordpress/api-fetch';

export default function refill( form ) {
	const formData = new FormData( form );

	const detail = {
		contactFormId: form.wpcf7.id,
		pluginVersion: form.wpcf7.pluginVersion,
		contactFormLocale: form.wpcf7.locale,
		unitTag: form.wpcf7.unitTag,
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
		path: `contact-form-7/v1/contact-forms/${ form.wpcf7.id }/refill`,
		method: 'GET',
	} ).then( response => {

		detail.apiResponse = response;

		wpcf7.triggerEvent( form.wpcf7.parent, 'refill', detail );

	} ).catch( error => console.error( error ) );
}
