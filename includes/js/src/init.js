export default function init( form ) {
	form.wpcf7 = {
		id: parseInt(
			form.querySelector( 'input[name="_wpcf7"]' ).value,
			10
		),

		status: form.getAttribute( 'data-status' ),

		pluginVersion: form.querySelector(
			'input[name="_wpcf7_version"]'
		).value,

		locale: form.querySelector(
			'input[name="_wpcf7_locale"]'
		).value,

		unitTag: form.querySelector(
			'input[name="_wpcf7_unit_tag"]'
		).value,

		containerPost: parseInt(
			form.querySelector( 'input[name="_wpcf7_container_post"]' ).value,
			10
		),
	};

	form.addEventListener( 'submit', event => {
		if ( typeof window.FormData === 'function' ) {
			wpcf7.submit( form );
			event.preventDefault();
		}
	} );
}
