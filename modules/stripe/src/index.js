document.addEventListener( 'DOMContentLoaded', event => {

	if ( typeof window.wpcf7_stripe === 'undefined' ) {
		console.error( "window.wpcf7_stripe is not defined." );
		return;
	}

	if ( typeof window.Stripe !== 'function' ) {
		console.error( "window.Stripe is not defined." );
		return;
	}

	const stripe = Stripe( wpcf7_stripe.publishable_key );
	const elements = stripe.elements();

	document.addEventListener( 'wpcf7submit', event => {
		const unitTag = event.detail.unitTag;
		const form = document.querySelector( `#${ unitTag } form` );

		const hiddenInput = document.querySelector(
			`#${ unitTag } .wpcf7-stripe input[name="_wpcf7_stripe_payment_intent"]`
		);

		if ( ! hiddenInput ) {
			return;
		}

		hiddenInput.setAttribute( 'value', '' );

		if ( 'payment_required' === event.detail.status ) {
			const paymentIntent = event.detail.apiResponse.stripe.payment_intent;

			if ( paymentIntent.id ) {
				hiddenInput.setAttribute( 'value', paymentIntent.id );
			}

			const card = elements.getElement( 'card' ) || elements.create( 'card' );

			card.mount( `#${ unitTag } .wpcf7-stripe .card-element` );
			card.clear();

			const displayError = document.querySelector(
				`#${ unitTag } .wpcf7-stripe .card-errors`
			);

			card.addEventListener( 'change', event => {
				if ( event.error ) {
					displayError.textContent = event.error.message;
				} else {
					displayError.textContent = '';
				}
			} );

			const button = document.querySelector(
				`#${ unitTag } .wpcf7-stripe button`
			);

			button.disabled = false;

			button.addEventListener( 'click', event => {
				button.disabled = true;

				stripe.confirmCardPayment( paymentIntent.client_secret, {
					payment_method: {
						card: card,
					}
				} ).then( result => {
					if ( result.error ) {
						displayError.textContent = result.error.message;
					} else if ( 'succeeded' === result.paymentIntent.status ) {
						wpcf7.submit( form );
					}

					button.disabled = false;
				} );
			} );
		}
	} );
} );
