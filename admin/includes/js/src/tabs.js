const scope = document.querySelector( '#contact-form-editor' );

const init = () => {

	if ( ! scope ) {
		return;
	}

	const tabLists = scope.querySelectorAll( ':scope > [role="tablist"]' );

	tabLists.forEach( tabList => {
		const tabs = tabList.querySelectorAll( ':scope > [role="tab"]' );

		let tabFocus = parseInt( tabList.dataset.activeTab );

		tabList.addEventListener( 'keydown', event => {
			if ( [ 'ArrowLeft', 'ArrowRight' ].includes( event.key ) ) {
				tabs[ tabFocus ].setAttribute( 'tabindex', '-1' );

				if ( 'ArrowLeft' === event.key ) {
					tabFocus -= 1;

					if ( tabFocus < 0 ) {
						tabFocus = tabs.length - 1;
					}
				}

				if ( 'ArrowRight' === event.key ) {
					tabFocus += 1;

					if ( tabs.length <= tabFocus ) {
						tabFocus = 0;
					}
				}

				tabs[ tabFocus ].setAttribute( 'tabindex', '0' );
				tabs[ tabFocus ].focus();

				tabList.dataset.activeTab = tabFocus;
			}
		} );

		tabs.forEach( tab => {
			tab.addEventListener( 'click', event => {
				const controls = tab.getAttribute( 'aria-controls' );

				if ( ! controls ) {
					return;
				}

				const activePanel = controls.split( ' ' ).reduceRight(
					( accumulator, currentValue ) => {
						const control = scope.querySelector( `#${ currentValue }` );

						if ( control ) {
							accumulator = control;
						}

						return accumulator;
					},
					null
				);

				if ( ! activePanel ) {
					return;
				}

				tabList.querySelectorAll(
					':scope > [aria-selected="true"]'
				).forEach( tab => {
					tab.setAttribute( 'aria-selected', 'false' );
				} );

				tab.setAttribute( 'aria-selected', 'true' );

				scope.querySelectorAll(
					':scope > [role="tabpanel"]'
				).forEach( panel => {
					panel.setAttribute( 'hidden', 'hidden' );
				} );

				activePanel.removeAttribute( 'hidden' );

				document.querySelectorAll(
					'input[name="active-tab"]'
				).forEach( input => {
					input.value = activePanel.id;
				} );
			} );
		} );
	} );
};


export {
	init,
};
