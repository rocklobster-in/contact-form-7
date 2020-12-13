export default function setStatus( form, status ) {
	const prevStatus = form.getAttribute( 'data-status' );

	form.wpcf7.status = status;
	form.setAttribute( 'data-status', status );
	form.classList.add( status );

	if ( prevStatus && prevStatus !== status ) {
		form.classList.remove( prevStatus );
	}
}
