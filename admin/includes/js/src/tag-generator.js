import { init as v1Init } from './tag-generator-v1';
import { init as v2Init } from './tag-generator-v2';

const init = () => {
	document.querySelectorAll(
		'[data-taggen="open-dialog"]'
	).forEach( button => {
		button.addEventListener( 'click', event => {
			const dialog = document.querySelector( `#${ button.dataset.target }` );

			if ( dialog ) {
				const form = dialog.querySelector( 'form.tag-generator-panel' );

				if ( form ) {
					if ( '1' === form.dataset.version ) {
						v1Init( form );
					} else if ( '2' === form.dataset.version ) {
						v2Init( form );
					}
				}

				dialog.showModal();
			}
		} );
	} );

	document.querySelectorAll(
		'dialog.tag-generator-dialog'
	).forEach( dialog => {

		dialog.querySelectorAll(
			'[data-taggen="close-dialog"]'
		).forEach( button => {
			button.addEventListener( 'click', event => dialog.close( '' ) );
		} );

		dialog.querySelectorAll(
			'[data-taggen="insert-tag"], .insert-tag'
		).forEach( button => {
			button.addEventListener( 'click', event => {
				const tagField = dialog.querySelector( '[data-tag-part="tag"], .tag' );
				dialog.close( tagField?.value );
			} );
		} );

		dialog.addEventListener( 'close', event => {
			const textarea = document.querySelector( 'textarea#wpcf7-form' );

			if ( null === textarea ) {
				return;
			}

			if ( '' === dialog.returnValue ) {
				return;
			}

			const offset = textarea.selectionEnd ?? 0;

			if ( 0 === offset ) {
				dialog.returnValue += "\n\n";
			}

			textarea.value =
				textarea.value.substring( 0, offset ) +
				dialog.returnValue +
				textarea.value.substring( offset );

			textarea.selectionStart = offset;
			textarea.selectionEnd = offset + dialog.returnValue.length;

			textarea.focus();
		} );
	} );
};


export {
	init,
};
