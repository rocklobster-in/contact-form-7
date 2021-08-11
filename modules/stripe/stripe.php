<?php

include_once path_join( WPCF7_PLUGIN_MODULES_DIR, 'stripe/service.php' );
include_once path_join( WPCF7_PLUGIN_MODULES_DIR, 'stripe/api.php' );


add_action(
	'wpcf7_init',
	'wpcf7_stripe_register_service',
	10, 0
);

function wpcf7_stripe_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_category( 'payments',
		__( 'Payments', 'contact-form-7' )
	);

	$integration->add_service( 'stripe',
		WPCF7_Stripe::get_instance()
	);
}


add_action(
	'wpcf7_enqueue_scripts',
	'wpcf7_stripe_enqueue_scripts',
	10, 0
);

function wpcf7_stripe_enqueue_scripts() {
	$service = WPCF7_Stripe::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	wp_enqueue_style( 'wpcf7-stripe',
		wpcf7_plugin_url( 'modules/stripe/style.css' ),
		array(), WPCF7_VERSION, 'all'
	);

	wp_enqueue_script( 'stripe',
		'https://js.stripe.com/v3/',
		array(), null
	);

	$assets = array();

	$asset_file = wpcf7_plugin_path( 'modules/stripe/index.asset.php' );

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	}

	$assets = wp_parse_args( $assets, array(
		'src' => wpcf7_plugin_url( 'modules/stripe/index.js' ),
		'dependencies' => array(
			'wp-polyfill',
		),
		'version' => WPCF7_VERSION,
	) );

	wp_enqueue_script(
		'wpcf7-stripe',
		$assets['src'],
		array_merge( array(
			'contact-form-7',
			'stripe',
		), $assets['dependencies'] ),
		$assets['version'],
		true
	);

	$api_keys = $service->get_api_keys();

	if ( $api_keys['publishable'] ) {
		wp_localize_script( 'wpcf7-stripe', 'wpcf7_stripe', array(
			'publishable_key' => $api_keys['publishable'],
		) );
	}
}


add_filter(
	'wpcf7_skip_spam_check',
	'wpcf7_stripe_skip_spam_check',
	10, 2
);

function wpcf7_stripe_skip_spam_check( $skip_spam_check, $submission ) {
	$service = WPCF7_Stripe::get_instance();

	if ( ! $service->is_active() ) {
		return $skip_spam_check;
	}

	if ( ! empty( $_POST['_wpcf7_stripe_payment_intent'] ) ) {
		$pi_id = trim( $_POST['_wpcf7_stripe_payment_intent'] );
		$payment_intent = $service->api()->retrieve_payment_intent( $pi_id );

		if ( isset( $payment_intent['status'] )
		and ( 'succeeded' === $payment_intent['status'] ) ) {
			$submission->payment_intent = $pi_id;
		}
	}

	if ( ! empty( $submission->payment_intent )
	and $submission->verify_posted_data_hash() ) {
		$skip_spam_check = true;
	}

	return $skip_spam_check;
}


add_action(
	'wpcf7_before_send_mail',
	'wpcf7_stripe_before_send_mail',
	10, 3
);

function wpcf7_stripe_before_send_mail( $contact_form, &$abort, $submission ) {
	$service = WPCF7_Stripe::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	$tags = $contact_form->scan_form_tags( array( 'type' => 'stripe' ) );

	if ( ! $tags ) {
		return;
	}

	if ( ! empty( $submission->payment_intent ) ) {
		return;
	}

	$tag = $tags[0];
	$amount = $tag->get_option( 'amount', 'int', true );
	$currency = $tag->get_option( 'currency', '[a-zA-Z]{3}', true );

	$payment_intent_params = apply_filters(
		'wpcf7_stripe_payment_intent_parameters',
		array(
			'amount' => $amount ? absint( $amount ) : null,
			'currency' => $currency ? strtolower( $currency ) : null,
			'receipt_email' => $submission->get_posted_data( 'your-email' ),
		)
	);

	$payment_intent = $service->api()->create_payment_intent(
		$payment_intent_params
	);

	if ( ! empty( $payment_intent ) ) {
		// this should be done in more elegant way
		add_action( 'wpcf7_feedback_response',
			function( $response, $result ) use ( $payment_intent ) {
				$response['stripe'] = array(
					'payment_intent' => array(
						'id' => $payment_intent['id'],
						'client_secret' => $payment_intent['client_secret'],
					),
				);

				return $response;
			},
			10, 2
		);

		$submission->set_status( 'payment_required' );
		$submission->set_response(
			__( "Payment is required. Please pay by credit card.", 'contact-form-7' )
		);
	}

	$abort = true;
}


add_filter(
	'wpcf7_special_mail_tags',
	'wpcf7_stripe_smt',
	10, 4
);

function wpcf7_stripe_smt( $output, $tag_name, $html, $mail_tag ) {
	if ( '_stripe_payment_link' === $tag_name ) {
		$submission = WPCF7_Submission::get_instance();

		if ( ! empty( $submission->payment_intent ) ) {
			$output = sprintf(
				'https://dashboard.stripe.com/payments/%s',
				urlencode( $submission->payment_intent )
			);
		}
	}

	return $output;
}


add_action(
	'wpcf7_init',
	'wpcf7_add_form_tag_stripe',
	10, 0
);

function wpcf7_add_form_tag_stripe() {
	wpcf7_add_form_tag(
		'stripe',
		'wpcf7_stripe_form_tag_handler',
		array(
			'display-block' => true,
		)
	);
}


function wpcf7_stripe_form_tag_handler( $tag ) {
	$button_label = (string) reset( $tag->values );

	if ( empty( $button_label ) ) {
		$button_label = __( 'Pay Now', 'contact-form-7' );
	}

	$html = sprintf(
		'<div class="wpcf7-stripe">%1$s %2$s %3$s %4$s %5$s</div>',
		'<div class="card-element"></div>',
		'<div class="card-errors" role="alert"></div>',
		sprintf(
			'<button type="button">%1$s %2$s</button>',
			esc_html( $button_label ),
			'<span class="spinner"></span>'
		),
		'<input type="hidden" name="_wpcf7_stripe_payment_intent" value="" />',
		'<div class="powered-by-stripe">Powered by <span class="logo"></span></div>'
	);

	return $html;
}
