<?php

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
		$payment_intent = $service->retrieve_payment_intent( $pi_id );

		if ( isset( $payment_intent['status'] )
		and ( 'succeeded' === $payment_intent['status'] ) ) {
			$submission->payment_intent = $pi_id;
		}
	}

	if ( ! empty( $_POST['_wpcf7_posted_data_hash'] ) ) {
		$posted_data_hash = trim( $_POST['_wpcf7_posted_data_hash'] );

		if ( $posted_data_hash === $submission->get_posted_data_hash()
		and ! empty( $submission->payment_intent ) ) {
			$skip_spam_check = true;
		}
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

	$payment_intent = $service->create_payment_intent( array(
		'amount' => $amount ? absint( $amount ) : null,
		'currency' => $currency ? strtolower( $currency ) : null,
		'receipt_email' => $submission->get_posted_data( 'your-email' ),
	) );

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


if ( ! class_exists( 'WPCF7_Service' ) ) {
	return;
}

class WPCF7_Stripe extends WPCF7_Service {

	private static $instance;
	private $api_keys;

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		$option = WPCF7::get_option( 'stripe' );

		if ( isset( $option['api_keys']['publishable'] )
		and isset( $option['api_keys']['secret'] ) ) {
			$this->api_keys = array(
				'publishable' => $option['api_keys']['publishable'],
				'secret' => $option['api_keys']['secret'],
			);
		}
	}

	public function get_title() {
		return __( 'Stripe', 'contact-form-7' );
	}

	public function is_active() {
		return (bool) $this->get_api_keys();
	}

	public function get_api_keys() {
		return $this->api_keys;
	}

	public function get_categories() {
		return array( 'payments' );
	}

	public function icon() {
	}

	public function link() {
		echo wpcf7_link(
			'https://stripe.com/',
			'stripe.com'
		);
	}

	protected function log( $url, $request, $response ) {
		wpcf7_log_remote_request( $url, $request, $response );
	}

	private function default_headers() {
		// https://stripe.com/docs/building-plugins#setappinfo
		$app_info = array(
			'name' => 'WordPress Contact Form 7',
			'partner_id' => 'pp_partner_HHbvqLh1AaO7Am',
			'url' => 'https://contactform7.com/',
			'version' => WPCF7_VERSION,
		);

		$ua = array(
			'lang' => 'php',
			'lang_version' => PHP_VERSION,
			'application' => $app_info,
		);

		$headers = array(
			'Authorization' => sprintf( 'Bearer %s', $this->api_keys['secret'] ),
			'Stripe-Version' => '2020-08-27',
			'X-Stripe-Client-User-Agent' => json_encode( $ua ),
		);

		return $headers;
	}

	public function create_payment_intent( $args = '' ) {
		if ( ! $this->is_active() ) {
			return;
		}

		$args = wp_parse_args( $args, array(
			'amount' => 0,
			'currency' => '',
			'receipt_email' => '',
		) );

		$endpoint = 'https://api.stripe.com/v1/payment_intents';

		$request = array(
			'headers' => $this->default_headers(),
			'body' => $args,
		);

		$response = wp_remote_post( esc_url_raw( $endpoint ), $request );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			if ( WP_DEBUG ) {
				$this->log( $endpoint, $request, $response );
			}

			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		return $response_body;
	}

	public function retrieve_payment_intent( $id ) {
		$endpoint = sprintf(
			'https://api.stripe.com/v1/payment_intents/%s',
			urlencode( $id )
		);

		$request = array(
			'headers' => $this->default_headers(),
		);

		$response = wp_remote_get( esc_url_raw( $endpoint ), $request );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			if ( WP_DEBUG ) {
				$this->log( $endpoint, $request, $response );
			}

			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		return $response_body;
	}

	protected function menu_page_url( $args = '' ) {
		$args = wp_parse_args( $args, array() );

		$url = menu_page_url( 'wpcf7-integration', false );
		$url = add_query_arg( array( 'service' => 'stripe' ), $url );

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	protected function save_data() {
		WPCF7::update_option( 'stripe', array(
			'api_keys' => $this->api_keys,
		) );
	}

	protected function reset_data() {
		$this->api_keys = null;
		$this->save_data();
	}

	public function load( $action = '' ) {
		if ( 'setup' == $action and 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'wpcf7-stripe-setup' );

			if ( ! empty( $_POST['reset'] ) ) {
				$this->reset_data();
				$redirect_to = $this->menu_page_url( 'action=setup' );
			} else {
				$publishable = isset( $_POST['publishable'] ) ?
					trim( $_POST['publishable'] ) : '';
				$secret = isset( $_POST['secret'] ) ? trim( $_POST['secret'] ) : '';

				if ( $publishable and $secret ) {
					$this->api_keys = array(
						'publishable' => $publishable,
						'secret' => $secret,
					);
					$this->save_data();

					$redirect_to = $this->menu_page_url( array(
						'message' => 'success',
					) );
				} else {
					$redirect_to = $this->menu_page_url( array(
						'action' => 'setup',
						'message' => 'invalid',
					) );
				}
			}

			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	public function admin_notice( $message = '' ) {
		if ( 'invalid' == $message ) {
			echo sprintf(
				'<div class="notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
				esc_html( __( "Error", 'contact-form-7' ) ),
				esc_html( __( "Invalid key values.", 'contact-form-7' ) )
			);
		}

		if ( 'success' == $message ) {
			echo sprintf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( __( 'Settings saved.', 'contact-form-7' ) )
			);
		}
	}

	public function display( $action = '' ) {
		// https://stripe.com/docs/partners/support#intro
		echo '<p>' . sprintf(
			esc_html( __( 'Stripe is a simple and powerful way to accept payments online. Stripe has no setup fees, no monthly fees, and no hidden costs. Millions of businesses rely on Stripe’s software tools to accept payments securely and expand globally. For details, see %s.', 'contact-form-7' ) ),
			wpcf7_link(
				__( 'https://contactform7.com/recaptcha/', 'contact-form-7' ),
				__( 'reCAPTCHA (v3)', 'contact-form-7' )
			)
		) . '</p>';

		if ( $this->is_active() ) {
			echo sprintf(
				'<p class="dashicons-before dashicons-yes">%s</p>',
				esc_html( __( "Stripe is active on this site.", 'contact-form-7' ) )
			);
		}

		if ( 'setup' == $action ) {
			$this->display_setup();
		} else {
			echo sprintf(
				'<p><a href="%1$s" class="button">%2$s</a></p>',
				esc_url( $this->menu_page_url( 'action=setup' ) ),
				esc_html( __( 'Setup Integration', 'contact-form-7' ) )
			);
		}
	}

	private function display_setup() {
		$api_keys = $this->get_api_keys();

		if ( $api_keys ) {
			$publishable = $api_keys['publishable'];
			$secret = $api_keys['secret'];
		} else {
			$publishable = '';
			$secret = '';
		}

?>
<form method="post" action="<?php echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
<?php wp_nonce_field( 'wpcf7-stripe-setup' ); ?>
<table class="form-table">
<tbody>
<tr>
	<th scope="row"><label for="publishable"><?php echo esc_html( __( 'Publishable Key', 'contact-form-7' ) ); ?></label></th>
	<td><?php
		if ( $this->is_active() ) {
			echo esc_html( $publishable );
			echo sprintf(
				'<input type="hidden" value="%s" id="publishable" name="publishable" />',
				esc_attr( $publishable )
			);
		} else {
			echo sprintf(
				'<input type="text" aria-required="true" value="%s" id="publishable" name="publishable" class="regular-text code" />',
				esc_attr( $publishable )
			);
		}
	?></td>
</tr>
<tr>
	<th scope="row"><label for="secret"><?php echo esc_html( __( 'Secret Key', 'contact-form-7' ) ); ?></label></th>
	<td><?php
		if ( $this->is_active() ) {
			echo esc_html( wpcf7_mask_password( $secret ) );
			echo sprintf(
				'<input type="hidden" value="%s" id="secret" name="secret" />',
				esc_attr( $secret )
			);
		} else {
			echo sprintf(
				'<input type="text" aria-required="true" value="%s" id="secret" name="secret" class="regular-text code" />',
				esc_attr( $secret )
			);
		}
	?></td>
</tr>
</tbody>
</table>
<?php
		if ( $this->is_active() ) {
			submit_button(
				_x( 'Remove Keys', 'API keys', 'contact-form-7' ),
				'small', 'reset'
			);
		} else {
			submit_button( __( 'Save Changes', 'contact-form-7' ) );
		}
?>
</form>
<?php
	}
}
