import { init as initTabs } from './tabs';
import { init as initConfigFields } from './config-errors';
import { init as initWelcomePanel } from './welcome-panel';
import { init as initTagGenerator } from './tag-generator';
import { init as initBeforeUnload } from './before-unload';
import { toggleFieldset } from './utils';


document.addEventListener( 'DOMContentLoaded', event => {
	initTabs();
	initConfigFields();
	initWelcomePanel();
	initTagGenerator();
	initBeforeUnload();

	const titleField = document.querySelector( 'input#title' );

	if ( titleField && '' === titleField.value ) {
		titleField.focus();
	}

	document.querySelectorAll(
		'.contact-form-editor-box-mail span.mailtag'
	).forEach( mailtag => {
		mailtag.addEventListener( 'click', event => {
			const range = document.createRange();
			range.selectNodeContents( mailtag );
			window.getSelection().addRange( range );
		} );
	} );

	document.querySelectorAll(
		'[data-toggle]'
	).forEach( toggle => {
		toggleFieldset( toggle );

		toggle.addEventListener( 'change', event => {
			toggleFieldset( toggle );
		} );
	} );
} );
