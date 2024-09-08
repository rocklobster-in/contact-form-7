import { init as initTabs } from './tabs';
import { init as initConfigFields } from './config-errors';
import { init as initWelcomePanel } from './welcome-panel';
import { init as initTagGenerator } from './tag-generator';


document.addEventListener( 'DOMContentLoaded', event => {
	initTabs();
	initConfigFields();
	initWelcomePanel();
	initTagGenerator();

	const titleField = document.querySelector( 'input#title' );

	if ( titleField && '' === titleField.value ) {
		titleField.focus();
	}

	const toggleFieldset = toggle => {
		const target = document.querySelector( `#${ toggle.dataset?.toggle }` );

		if ( ! target ) {
			return;
		}

		if ( toggle.checked ) {
			target.classList.remove( 'hidden' );
		} else {
			target.classList.add( 'hidden' );
		}
	};

	document.querySelectorAll(
		'[data-toggle]'
	).forEach( toggle => {
		toggleFieldset( toggle );

		toggle.addEventListener( 'change', event => {
			toggleFieldset( toggle );
		} );
	} );
} );
