
document.querySelectorAll(
	'#tag-generator-list button'
).forEach( button => {
	button.addEventListener( 'click', event => {
		const modal = document.querySelector( `#${ button.dataset.target }` );
		modal?.showModal();
	} );
} );

document.querySelectorAll(
	'dialog button.close-modal'
).forEach( button => {
	button.addEventListener( 'click', event => {
		const modal = button.closest( 'dialog' );
		modal?.close();
	} );
} );
