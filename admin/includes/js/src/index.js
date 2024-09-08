import { init as initTabs } from './tabs';
import { init as initConfigFields } from './config-errors';
import { init as initWelcomePanel } from './welcome-panel';
import { init as initTagGenerator } from './tag-generator';
import { toggleFieldset } from './utils';


document.addEventListener( 'DOMContentLoaded', event => {
	initTabs();
	initConfigFields();
	initWelcomePanel();
	initTagGenerator();

	const titleField = document.querySelector( 'input#title' );

	if ( titleField && '' === titleField.value ) {
		titleField.focus();
	}

	document.querySelectorAll(
		'[data-toggle]'
	).forEach( toggle => {
		toggleFieldset( toggle );

		toggle.addEventListener( 'change', event => {
			toggleFieldset( toggle );
		} );
	} );
} );
