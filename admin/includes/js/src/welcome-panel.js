const welcomePanel = document.querySelector( '#wpcf7-welcome-panel' );

const init = () => {
	if ( ! welcomePanel ) {
		return;
	}

	const showOpt = document.querySelector( '#wpcf7-welcome-panel-show' );

	welcomePanel.querySelectorAll(
		'.welcome-panel-close'
	).forEach( button => {
		button.addEventListener( 'click', event => {
			update( 0 );
			showOpt?.removeAttribute( 'checked' );
			event.preventDefault();
		} );
	} );

	showOpt?.addEventListener( 'click', event => {
		if ( showOpt?.checked ) {
			update( 1 );
		} else {
			update( 0 );
		}
	} );
};


const update = visibility => {
	const nonce = welcomePanel.querySelector(
		'#welcomepanelnonce'
	)?.value;

	if ( ! nonce ) {
		return;
	}

	const body = new FormData();
	body.append( 'action', 'wpcf7-update-welcome-panel' );
	body.append( 'visible', visibility );
	body.append( 'welcomepanelnonce', nonce );

	fetch( new Request( ajaxurl, {
		method: 'POST',
		body,
	} ) );

	if ( visibility ) {
		welcomePanel.classList.remove( 'hidden' );
	} else {
		welcomePanel.classList.add( 'hidden' );
	}
};


export {
	init,
};
