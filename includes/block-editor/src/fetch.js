import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const fetchContactForms = async ( options ) => {
	const endpoint = '/contact-form-7/v1/contact-forms';

	return apiFetch( {
		path: addQueryArgs( endpoint, {
			posts_per_page: 20,
			orderby: 'modified',
			order: 'DESC',
			...options
		} ),
	} ).then( response => {
		return response;
	} );
};

export default fetchContactForms;
