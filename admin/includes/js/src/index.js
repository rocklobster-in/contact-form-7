import { __ } from '@wordpress/i18n';

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

	document.querySelector(
		'#wpcf7-admin-form-element'
	)?.addEventListener( 'submit', event => {
		const inputAction = document.querySelector(
			'#wpcf7-admin-form-element [name="action"]'
		);

		const inputNonce = document.querySelector(
			'#wpcf7-admin-form-element [name="_wpnonce"]'
		);

		if ( 'wpcf7-save' === event.submitter?.name ) {
			if ( inputAction ) {
				inputAction.value = 'save';
			}

			if ( inputNonce ) {
				inputNonce.value = wpcf7.nonce.save;
			}

			document.querySelectorAll(
				'#wpcf7-admin-form-element #publishing-action .spinner'
			).forEach( spinner => {
				spinner.classList.add( 'is-active' );
			} );
		}

		if ( 'wpcf7-copy' === event.submitter?.name ) {
			if ( inputAction ) {
				inputAction.value = 'copy';
			}

			if ( inputNonce ) {
				inputNonce.value = wpcf7.nonce.copy;
			}
		}

		if ( 'wpcf7-delete' === event.submitter?.name ) {
			const confirmed = window.confirm( __( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", 'contact-form-7' ) );

			if ( confirmed ) {
				if ( inputAction ) {
					inputAction.value = 'delete';
				}

				if ( inputNonce ) {
					inputNonce.value = wpcf7.nonce.delete;
				}
			} else {
				event.preventDefault();
			}
		}
	} );

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

	document.querySelectorAll(
		'#wpcf7-ctct-enable-contact-list, #wpcf7-sendinblue-enable-contact-list, #wpcf7-sendinblue-enable-transactional-email'
	).forEach( checkbox => {
		checkbox.addEventListener( 'change', event => {
			if ( checkbox.checked ) {
				checkbox.closest( 'tr' ).classList.remove( 'inactive' );
			} else {
				checkbox.closest( 'tr' ).classList.add( 'inactive' );
			}
		} );
	} );

} );
