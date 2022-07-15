import { setStatus } from './status';
import { triggerEvent } from './event';
import { clearResponse } from './submit';
import { apiFetch } from './api-fetch';

export default function reset( form ) {
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
		endpoint: `contact-forms/${ form.wpcf7.id }/refill`,
		method: 'GET',
		wpcf7: {
			endpoint: 'refill',
			form,
			detail,
		},
	} ).then( response => {

		if ( form.wpcf7.resetOnMailSent ) {
			delete form.wpcf7.resetOnMailSent;
			setStatus( form, 'mail_sent' );
		} else {
			setStatus( form, 'init' );
		}

		detail.apiResponse = response;

		triggerEvent( form, 'reset', detail );

	} ).catch( error => console.error( error ) );
}

apiFetch.use( ( options, next ) => {
	if ( options.wpcf7 && 'refill' === options.wpcf7.endpoint ) {
		const { form, detail } = options.wpcf7;

		clearResponse( form );
		setStatus( form, 'resetting' );
	}

	return next( options );
} );

// Refill for Really Simple CAPTCHA
export const resetCaptcha = ( form, refill ) => {
	for ( const name in refill ) {
		const url = refill[ name ];

		form.querySelectorAll( `input[name="${ name }"]` ).forEach( input => {
			input.value = '';
		} );

		form.querySelectorAll(
			`img.wpcf7-captcha-${ name.replaceAll( ':', '' ) }`
		).forEach( img => {
			img.setAttribute( 'src', url );
		} );

		const match = /([0-9]+)\.(png|gif|jpeg)$/.exec( url );

		if ( match ) {
			form.querySelectorAll(
				`input[name="_wpcf7_captcha_challenge_${ name }"]`
			).forEach( input => {
				input.value = match[ 1 ];
			} );
		}
	}
};

// Refill for quiz fields
export const resetQuiz = ( form, refill ) => {
	for ( const name in refill ) {
		const question = refill[ name ][ 0 ];
		const hashedAnswer = refill[ name ][ 1 ];

		form.querySelectorAll(
			`.wpcf7-form-control-wrap[data-name="${ name }"]`
		).forEach( wrap => {
			wrap.querySelector( `input[name="${ name }"]` ).value = '';
			wrap.querySelector( '.wpcf7-quiz-label' ).textContent = question;

			wrap.querySelector(
				`input[name="_wpcf7_quiz_answer_${ name }"]`
			).value = hashedAnswer;
		} );
	}
};
