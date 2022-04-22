export const setStatus = ( form, status ) => {
	const defaultStatuses = new Map( [
		// 0: Status in API response, 1: Status in HTML class
		[ 'init', 'init' ],
		[ 'validation_failed', 'invalid' ],
		[ 'acceptance_missing', 'unaccepted' ],
		[ 'spam', 'spam' ],
		[ 'aborted', 'aborted' ],
		[ 'mail_sent', 'sent' ],
		[ 'mail_failed', 'failed' ],
		[ 'submitting', 'submitting' ],
		[ 'resetting', 'resetting' ],
		[ 'validating', 'validating' ],
		[ 'payment_required', 'payment-required' ],
	] );

	if ( defaultStatuses.has( status ) ) {
		status = defaultStatuses.get( status );
	}

	if ( ! Array.from( defaultStatuses.values() ).includes( status ) ) {
		status = status.replace( /[^0-9a-z]+/i, ' ' ).trim();
		status = status.replace( /\s+/, '-' );
		status = `custom-${ status }`;
	}

	const prevStatus = form.getAttribute( 'data-status' );

	form.wpcf7.status = status;
	form.setAttribute( 'data-status', status );
	form.classList.add( status );

	if ( prevStatus && prevStatus !== status ) {
		form.classList.remove( prevStatus );
	}

	return status;
};
