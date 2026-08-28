<?php

if ( ! class_exists( 'WPCF7_Service' ) ) {
	return;
}

class WPCF7_CleanTalk extends WPCF7_Service {

	const SDK_VERSION = '0.2.0';
	const MODERATE_URL = 'https://moderate.cleantalk.org/api2.0';
	const API_URL = 'https://api.cleantalk.org/';
	const BOT_DETECTOR_URL = 'https://fd.cleantalk.org/ct-bot-detector-wrapper.js';

	private static $instance;
	private $access_key;

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->access_key = WPCF7::get_option( 'cleantalk' );
	}


	public function get_title() {
		return __( 'CleanTalk', 'contact-form-7' );
	}


	public function is_active() {
		return ! empty( $this->access_key );
	}


	public function get_categories() {
		return array( 'spam_protection' );
	}


	public function icon() {
	}


	public function link() {
		echo wpcf7_link( 'https://cleantalk.org/my', 'CleanTalk Dashboard' );
	}


	public function verify() {
		$result = array( 'allow' => 1, 'comment' => '' );

		if ( defined( 'APBCT_VERSION' ) ) {
			return $result;
		}

		$submission = WPCF7_Submission::get_instance();
		$form_data = $submission->get_posted_data();

		$email = '';
		array_walk_recursive( $form_data, function( $value ) use ( &$email ) {
			if ( is_string( $value )
				&& preg_match( '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $value, $m )
			) {
				$email = $m[0];
			}
		} );

		$message = '';

		if ( ! empty( $form_data['your-message'] ) ) {
			$message = is_scalar( $form_data['your-message'] )
				? $form_data['your-message']
				: serialize( $form_data['your-message'] );
		}

		$agent = 'wordpress-contactform7-' . WPCF7_VERSION;
		$event_token = ! empty( $form_data['ct_bot_detector_event_token'] )
			? $form_data['ct_bot_detector_event_token']
			: '';
		$referrer = isset( $_SERVER['HTTP_REFERER'] )
			? $_SERVER['HTTP_REFERER'] : '';
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? $_SERVER['HTTP_USER_AGENT'] : '';

		$all_headers = '';

		if ( function_exists( 'apache_request_headers' ) ) {
			$headers = array_filter(
				apache_request_headers(),
				function( $v, $k ) {
					return strtolower( $k ) !== 'cookie';
				},
				ARRAY_FILTER_USE_BOTH
			);
			$all_headers = json_encode( $headers );
		}

		$body = array(
			'method_name'     => 'check_message',
			'auth_key'        => $this->access_key,
			'sender_ip'       => isset( $_SERVER['REMOTE_ADDR'] )
				? $_SERVER['REMOTE_ADDR'] : '',
			'sender_email'    => $email,
			'event_token'     => $event_token,
			'agent'           => $agent,
			'message'         => $message,
			'referrer'        => $referrer,
			'user_agent'      => $user_agent,
			'all_headers'     => $all_headers,
			'x_forwarded_for' => isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )
				? $_SERVER['HTTP_X_FORWARDED_FOR'] : '',
			'x_real_ip'       => isset( $_SERVER['HTTP_X_REAL_IP'] )
				? $_SERVER['HTTP_X_REAL_IP'] : '',
			'sender_info'     => json_encode( array(
				'REFFERRER'   => $referrer,
				'user_agent'  => $user_agent,
				'sdk_version' => self::SDK_VERSION,
			) ),
			'post_info'       => json_encode( array(
				'comment_type' => 'sdk_contact_form_' . $agent,
			) ),
		);

		$response = wp_remote_post( self::MODERATE_URL, array(
			'body' => json_encode( $body ),
		) );

		if ( ! is_wp_error( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( $data && isset( $data->allow, $data->comment ) ) {
				$result['allow'] = (int) $data->allow;
				$result['comment'] = $data->comment;
			}
		}

		$submission->push( 'cleantalk', array(
			'version' => self::SDK_VERSION,
			'comment' => $result['comment'],
		) );

		return $result;
	}


	protected function menu_page_url( $args = '' ) {
		$args = wp_parse_args( $args, array() );

		$url = menu_page_url( 'wpcf7-integration', false );
		$url = add_query_arg( array( 'service' => 'cleantalk' ), $url );

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}


	protected function save_data() {
		WPCF7::update_option( 'cleantalk', $this->access_key );
	}


	protected function reset_data() {
		$this->access_key = null;
		$this->save_data();
	}


	public function load( $action = '' ) {
		if ( 'setup' === $action and 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'wpcf7-cleantalk-setup' );

			if ( ! empty( $_POST['reset'] ) ) {
				$this->reset_data();
				$redirect_to = $this->menu_page_url( 'action=setup' );
			} else {
				$key = isset( $_POST['sitekey'] )
					? sanitize_text_field( $_POST['sitekey'] ) : '';

				if ( $key && $this->validate_access_key( $key ) ) {
					$this->access_key = $key;
					$this->save_data();
					WPCF7::update_option( 'cleantalk_warning', false );
					$redirect_to = $this->menu_page_url(
						array( 'message' => 'success' )
					);
				} else {
					$this->reset_data();
					WPCF7::update_option( 'cleantalk_warning', true );
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


	private function validate_access_key( $key ) {
		$response = wp_remote_post( self::API_URL, array(
			'body' => array(
				'method_name' => 'notice_paid_till',
				'auth_key'    => $key,
			),
		) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ) );

		return $body
			&& isset( $body->data->valid ) && $body->data->valid == 1
			&& isset( $body->data->product_id ) && $body->data->product_id == 1
			&& isset( $body->data->moderate ) && $body->data->moderate == 1;
	}


	public function admin_notice( $message = '' ) {
		if ( 'invalid' === $message ) {
			wp_admin_notice(
				sprintf(
					'<strong>%1$s</strong>: %2$s',
					esc_html( __( "Error", 'contact-form-7' ) ),
					esc_html( __( "Invalid key values.", 'contact-form-7' ) )
				),
				array( 'type' => 'error' )
			);
		}

		if ( 'success' === $message ) {
			wp_admin_notice(
				esc_html( __( "Settings saved.", 'contact-form-7' ) ),
				array( 'type' => 'success' )
			);
		}
	}


	public function display( $action = '' ) {
		echo sprintf(
			'<p>%s</p>',
			esc_html( __( "CleanTalk is a cloud anti-spam service that provides invisible spam protection without CAPTCHA.", 'contact-form-7' ) )
		);

		if ( $this->is_active() ) {
			echo sprintf(
				'<p class="dashicons-before dashicons-yes">%s</p>',
				esc_html( __( "CleanTalk is active on this site.", 'contact-form-7' ) )
			);
		} else {
			$admin_email = get_option( 'admin_email' );
			$site_url = home_url();
			$register_url = add_query_arg(
				array(
					'platform'     => 'wordpress',
					'product_name' => 'antispam',
					'email'        => $admin_email,
					'website'      => $site_url,
					'utm_source'   => 'admin_side',
					'utm_medium'   => 'get_access_key_button',
					'utm_campaign' => 'apbct_links',
				),
				'https://cleantalk.org/register'
			);
			echo wp_kses_post( sprintf(
				'<p>%s</p>',
				sprintf(
					__( 'Get an access key from your <a target="_blank" href="%1$s" rel="noopener noreferrer">Cloud Dashboard</a>, or <a href="%2$s" target="_blank" rel="noopener noreferrer">sign up</a> to create a new account.', 'contact-form-7' ),
					'https://cleantalk.org/my/?cp_mode=antispam',
					esc_url( $register_url )
				)
			) );
		}

		if ( 'setup' === $action ) {
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
		$sitekey = $this->access_key;
?>
<form method="post" action="<?php echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
<?php wp_nonce_field( 'wpcf7-cleantalk-setup' ); ?>
<table class="form-table">
<tbody>
<tr>
	<th scope="row"><label for="sitekey"><?php echo esc_html( __( 'Access Key', 'contact-form-7' ) ); ?></label></th>
	<td><?php
		if ( $this->is_active() ) {
			echo esc_html( $sitekey );
			echo sprintf(
				'<input type="hidden" value="%1$s" id="sitekey" name="sitekey" />',
				esc_attr( $sitekey )
			);
		} else {
			echo sprintf(
				'<input type="text" aria-required="true" value="%1$s" id="sitekey" name="sitekey" class="regular-text code" />',
				esc_attr( $sitekey )
			);
		}
	?></td>
</tr>
</tbody>
</table>
<?php
		if ( $this->is_active() ) {
			submit_button(
				_x( 'Remove Key', 'API key', 'contact-form-7' ),
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