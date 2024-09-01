const init = () => {
	document.querySelectorAll(
		'#contact-form-editor-tabs li'
	).forEach( tab => {
		tab.addEventListener( 'click', event => {
			switchTab( tab.dataset?.panel );
			event.preventDefault();
		} );

		tab.addEventListener( 'keyup', event => {
			if ( [ 'ArrowDown', 'ArrowRight' ].includes( event.key ) ) {
				const nextTab = tab.nextElementSibling;

				if ( nextTab ) {
					switchTab( nextTab.dataset?.panel );
				}
			}

			if ( [ 'ArrowUp', 'ArrowLeft' ].includes( event.key ) ) {
				const prevTab = tab.previousElementSibling;

				if ( prevTab ) {
					switchTab( prevTab.dataset?.panel );
				}
			}
		} );
	} );

	document.querySelectorAll(
		'.contact-form-editor-panel'
	).forEach( panel => {
		if ( panel.classList.contains( 'active' ) ) {
			document.querySelector(
				'#contact-form-editor'
			)?.setAttribute( 'data-active-tab', panel.id );
		} else {
			panel.style.setProperty( 'display', 'none' );
		}
	} );
};


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
			tab.setAttribute( 'tabindex', '0' );
			tab.focus();
		} else {
			tab.classList.remove( 'active' );
			tab.setAttribute( 'tabindex', '-1' );
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
	init,
};
