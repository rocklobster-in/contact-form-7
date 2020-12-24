export default function initSubmitButton( form ) {

	if ( ! form.querySelector( '.wpcf7-acceptance' )
	|| form.classList.contains( 'wpcf7-acceptance-as-validation' ) ) {
		return;
	}

	const checkAcceptance = () => {
		let accepted = true;

		form.querySelectorAll( '.wpcf7-acceptance' ).forEach( parent => {
			if ( ! accepted || parent.classList.contains( 'optional' ) ) {
				return;
			}

			const checkbox = parent.querySelector( 'input[type="checkbox"]' );

			if ( parent.classList.contains( 'invert' ) && checkbox.checked
			|| ! parent.classList.contains( 'invert' ) && ! checkbox.checked ) {
				accepted = false;
			}
		} );

		form.querySelectorAll( '.wpcf7-submit' ).forEach( button => {
			button.disabled = ! accepted;
		} );
	};

	checkAcceptance();

	if ( 'init' === form.wpcf7.status ) {
		form.addEventListener(
			'change',
			event => checkAcceptance()
		);
	}
}
