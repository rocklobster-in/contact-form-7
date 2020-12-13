export default function init( form ) {
	wpcf7.setStatus( form, 'init' );

	form.addEventListener( 'submit', event => {
		if ( typeof window.FormData === 'function' ) {
			wpcf7.submit( form );
			event.preventDefault();
		}
	} );
}
