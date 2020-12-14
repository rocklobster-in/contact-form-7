export default function init( form ) {
	if ( typeof window.FormData !== 'function' ) {
		return;
	}

	const formData = new FormData( form );

	form.wpcf7 = {
		id: parseInt( formData.get( '_wpcf7' ), 10 ),
		status: form.getAttribute( 'data-status' ),
		pluginVersion: formData.get( '_wpcf7_version' ),
		locale: formData.get( '_wpcf7_locale' ),
		unitTag: formData.get( '_wpcf7_unit_tag' ),
		containerPost: parseInt( formData.get( '_wpcf7_container_post' ), 10 ),
		formData,
	};

	form.addEventListener( 'submit', event => {
		if ( typeof window.FormData === 'function' ) {
			wpcf7.submit( form );
			event.preventDefault();
		}
	} );
}
