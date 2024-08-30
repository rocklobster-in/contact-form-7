const switchTab = id => {
	if ( ! id ) {
		return;
	}

	if ( ! document.querySelector( `.contact-form-editor-panel#${ id }` ) ) {
		return;
	}

	document.querySelector(
		'#contact-form-editor'
	)?.setAttribute( 'data-active-tab', id );

	document.querySelectorAll(
		'input[name="active-tab"]'
	).forEach( input => {
		input.value = id;
	} );

	document.querySelectorAll(
		'#contact-form-editor-tabs li'
	).forEach( tab => {
		if ( tab.dataset?.panel === id ) {
			tab.classList.add( 'active' );
		} else {
			tab.classList.remove( 'active' );
		}
	} );

	document.querySelectorAll(
		'.contact-form-editor-panel'
	).forEach( panel => {
		if ( panel.id === id ) {
			panel.classList.add( 'active' );
			panel.style.setProperty( 'display', 'block' );
		} else {
			panel.classList.remove( 'active' );
			panel.style.setProperty( 'display', 'none' );
		}
	} );
};


export {
	switchTab
};
