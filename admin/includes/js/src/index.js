import { generateTag } from './tag-generator';

document.querySelectorAll(
	'#tag-generator-list button'
).forEach( button => {
	button.addEventListener( 'click', event => {
		const dialog = document.querySelector( `#${ button.dataset.target }` );
		dialog?.showModal();
	} );
} );

document.querySelectorAll(
	'dialog.tag-generator-dialog'
).forEach( dialog => {

	dialog.querySelectorAll(
		'.close-modal'
	).forEach( button => {
		button.addEventListener( 'click', event => dialog.close() );
	} );

	dialog.querySelectorAll(
		'.insert-tag'
	).forEach( button => {
		button.addEventListener( 'click', event => {
			const tagField = dialog.querySelector( '.tag' );
			dialog.close( tagField?.value );
		} );
	} );

	dialog.addEventListener( 'close', event => {
		const textarea = document.querySelector( 'textarea#wpcf7-form' );

		if ( null === textarea ) {
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
