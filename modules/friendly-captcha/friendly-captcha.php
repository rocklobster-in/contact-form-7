<?php

add_action( 'wpcf7_init', 'wpcf7_friendly_captcha_register_service', 10, 0 );

function wpcf7_friendly_captcha_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_category( 'captcha',
		__( 'CAPTCHA', 'contact-form-7' )
	);

	$integration->add_service( 'friendly-captcha',
		WPCF7_FRIENDLY_CAPTCHA::get_instance()
	);
}

add_action( 'wp_enqueue_scripts', 'wpcf7_friendly_captcha_enqueue_scripts', 10, 0 );

function wpcf7_friendly_captcha_enqueue_scripts() {
	$service = WPCF7_FRIENDLY_CAPTCHA::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	/* Modern browsers will load this smaller bundle */
	wp_enqueue_script( 'friendly-captcha-widget-module',
		wpcf7_plugin_url('modules/friendly-captcha/widget.module.min.js'),
		array(),
		'0.6.1',
		true
	);

	/* Fallback for (very) old browsers */
	wp_enqueue_script( 'friendly-captcha-widget-fallback',
		wpcf7_plugin_url('modules/friendly-captcha/widget.polyfilled.min.js'),
		array(),
		'0.6.1',
		true
	);

	wp_enqueue_script( 'wpcf7-friendly-captcha',
		wpcf7_plugin_url( 'modules/friendly-captcha/script.js' ),
		array( 'friendly-captcha-widget-module', 'friendly-captcha-widget-fallback' ),
		WPCF7_VERSION,
		true
	);
}

function wpcf7_generate_friendly_captcha_widget_tag($sitekey, $theme) {
	return sprintf(
        '<div class="frc-captcha %s" data-sitekey="%s"></div>
		<noscript>You need to enable Javascript for the anti-spam check.</noscript>',
	$theme,
    $sitekey);
}

add_filter( 'script_loader_tag', 'wpcf7_transform_friendly_captcha_script_tags', 10, 3 );

function wpcf7_transform_friendly_captcha_script_tags( $tag, $handle, $src )
{
	if ( 'friendly-captcha-widget-module' == $handle) {
		return str_replace( '<script', '<script async defer type="module"', $tag );
	}

    if ( 'friendly-captcha-widget-fallback' == $handle) {
        return str_replace( '<script', '<script async defer nomodule', $tag );
	}
	
	return $tag;
}

add_filter( 'wpcf7_form_elements',
	'wpcf7_friendly_captcha_add_widget_if_missing', 100, 1
);

function wpcf7_friendly_captcha_add_widget_if_missing( $elements ) {
	// Check if a widget is already present (probably through a shortcode)
	if (preg_match('/<div.*class=".*frc-captcha.*".*<\/div>/', $elements)) {
		return $elements;
	}

    $service = WPCF7_FRIENDLY_CAPTCHA::get_instance();
    
	if ( ! $service->is_active() ) {
		return $elements;
    }

    $sitekey = $service->get_sitekey();
    $elements .= wpcf7_generate_friendly_captcha_widget_tag($sitekey, "");
    
    return $elements;
}

add_filter( 'wpcf7_spam', 'wpcf7_friendly_captcha_verify_response', 9, 2 );


function wpcf7_friendly_captcha_verify_response( $spam, $submission ) {
	if ( $spam ) {
		return $spam;
	}

	$service = WPCF7_FRIENDLY_CAPTCHA::get_instance();

	if ( ! $service->is_active() ) {
		return $spam;
    }

	$solution = isset( $_POST['frc-captcha-solution'] )
		? trim( $_POST['frc-captcha-solution'] ) : '';
		
	if ( empty( $solution ) ) {
		$spam = true;
		$submission->add_spam_log( array(
			'agent' => 'friendly-captcha',
			'reason' => __( 'FriendlyCaptcha solution value frc-captcha-solution was missing', 'contact-form-7' ),
		) );
	}  elseif ($service->verify( $solution )) {
		$spam = false;
	} else {
		$spam = true;
		if ( '.UNSTARTED' === $solution ) {
			$submission->add_spam_log( array(
				'agent' => 'friendly-captcha',
				'reason' => __( 'FriendlyCaptcha widget was not started yet', 'contact-form-7' ),
			) );
		} elseif ( '.FETCHING' === $solution ) {
			$submission->add_spam_log( array(
				'agent' => 'friendly-captcha',
				'reason' => __( 'FriendlyCaptcha widget was still fetching a puzzle', 'contact-form-7' ),
			) );
		}
		elseif ( '.UNFINISHED' === $solution ) {
			$submission->add_spam_log( array(
				'agent' => 'friendly-captcha',
				'reason' => __( 'FriendlyCaptcha widget was not done solving yet', 'contact-form-7' ),
			) );
		} elseif ( '.ERROR' === $solution ) {
			$submission->add_spam_log( array(
				'agent' => 'friendly-captcha',
				'reason' => __( 'FriendlyCaptcha widget had an (internal) error', 'contact-form-7' ),
			) );
		} else {
			$submission->add_spam_log( array(
				'agent' => 'friendly-captcha',
				'reason' => sprintf(
					__( 'Problem with FriendlyCaptcha response: %s', 'contact-form-7' ),
					$service->get_last_error()
				),
			) );
		}
	}

	return $spam;
}

function wpcf7_friendly_captcha_widget_shortcode( $form_tag ){
	$service = WPCF7_FRIENDLY_CAPTCHA::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	$a = array(
		'sitekey' => $service->get_sitekey(),
		'class' => $form_tag->get_class_option("")
	);

	return wpcf7_generate_friendly_captcha_widget_tag($a['sitekey'], $a['class']);
}

add_action( 'wpcf7_init', 'wpcf7_friendly_captcha_add_form_tag_friendly_captcha', 10, 0 );

function wpcf7_friendly_captcha_add_form_tag_friendly_captcha() {
	$service = WPCF7_FRIENDLY_CAPTCHA::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	wpcf7_add_form_tag("friendlycaptcha", "wpcf7_friendly_captcha_widget_shortcode", array("theme"));	
}

if ( ! class_exists( 'WPCF7_Service' ) ) {
	return;
}

class WPCF7_FRIENDLY_CAPTCHA extends WPCF7_Service {

	private static $instance;
	private $sitekeys;
    private $last_error;
    private $last_success_status;

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		$this->sitekeys = WPCF7::get_option( 'friendly-captcha' );
	}

	public function get_title() {
		return __( 'FriendlyCaptcha', 'contact-form-7' );
	}

	public function is_active() {
        $sitekey = $this->get_sitekey();
        $secret = $this->get_secret( $sitekey );
		return $sitekey && $secret;
	}

	public function get_categories() {
		return array( 'captcha' );
	}

	public function icon() {
	}

	public function link() {
		echo wpcf7_link(
			'https://friendlycaptcha.com',
			'friendlycaptcha.com'
		);
	}

	public function get_global_sitekey() {
		static $sitekey = '';

		if ( $sitekey ) {
			return $sitekey;
		}

		if ( defined( 'WPCF7_FRIENDLY_CAPTCHA_SITEKEY' ) ) {
			$sitekey = WPCF7_FRIENDLY_CAPTCHA_SITEKEY;
		}

		$sitekey = apply_filters( 'wpcf7_friendly_captcha_sitekey', $sitekey );

		return $sitekey;
	}

	public function get_global_secret() {
		static $secret = '';

		if ( $secret ) {
			return $secret;
		}

		if ( defined( 'WPCF7_FRIENDLY_CAPTCHA_SECRET' ) ) {
			$secret = WPCF7_FRIENDLY_CAPTCHA_SECRET;
		}

		$secret = apply_filters( 'wpcf7_friendly_captcha_secret', $secret );

		return $secret;
	}

	public function get_sitekey() {
		if ( $this->get_global_sitekey() && $this->get_global_secret() ) {
			return $this->get_global_sitekey();
		}

		if ( empty( $this->sitekeys )
		or ! is_array( $this->sitekeys ) ) {
			return false;
		}

		$sitekeys = array_keys( $this->sitekeys );

		return $sitekeys[0];
	}

	public function get_secret( $sitekey ) {
		if ( $this->get_global_sitekey() && $this->get_global_secret() ) {
			return $this->get_global_secret();
		}

		$sitekeys = (array) $this->sitekeys;

		if ( isset( $sitekeys[$sitekey] ) ) {
			return $sitekeys[$sitekey];
		} else {
			return false;
		}
	}

	protected function log( $url, $request, $response ) {
		wpcf7_log_remote_request( $url, $request, $response );
	}

	public function verify( $solution ) {
        $is_valid_solution = false;

		if ( empty( $solution ) or ! $this->is_active() ) {
			return $is_valid_solution;
        }
        
		$endpoint = 'https://friendlycaptcha.com/api/v1/siteverify';

		$sitekey = $this->get_sitekey();
		$secret = $this->get_secret( $sitekey );

		$request = array(
			'body' => array(
				'secret' => $secret,
				'solution' => $solution,
			),
        );
        
		$response = wp_remote_post( esc_url_raw( $endpoint ), $request );

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			if ( WP_DEBUG ) {
				$this->log( $endpoint, $request, $response );
			}
            // Better safe than sorry, if the request is non-200 we can not verify the response
            // Either the user's credentials are wrong (e.g. wrong sitekey, api key) or the friendly
			// captcha servers are unresponsive.
			
			// TODO notify site admin somehow
			return true;
        }

		$response_body = wp_remote_retrieve_body( $response );
		$response_body = json_decode( $response_body, true );

		$this->last_success_status = $success = isset( $response_body['success'] )
			? $response_body['success']
            : false;

        $this->last_error = isset( $response_body['errorCodes'] )
			? reset($response_body['errorCodes'])
			: '';

        $is_valid_solution = $success;

		$is_valid_solution = apply_filters( 'wpcf7_friendly_captcha_verify_response',
			$is_valid_solution, $response_body );

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$submission->friendly_captcha = array(
				'version' => '0.6.1',
				'response' => $response_body,
			);
		}

		return $is_valid_solution;
	}

	public function get_last_error() {
		return $this->last_error;
	}

	public function get_last_success_status() {
		return $this->last_success_status;
	}

	protected function menu_page_url( $args = '' ) {
		$args = wp_parse_args( $args, array() );

		$url = menu_page_url( 'wpcf7-integration', false );
		$url = add_query_arg( array( 'service' => 'friendly-captcha' ), $url );

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	protected function save_data() {
		WPCF7::update_option( 'friendly-captcha', $this->sitekeys );
	}

	protected function reset_data() {
		$this->sitekeys = null;
		$this->save_data();
	}

	public function load( $action = '' ) {
		if ( 'setup' == $action and 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'wpcf7-friendly-captcha-setup' );

			if ( ! empty( $_POST['reset'] ) ) {
				$this->reset_data();
				$redirect_to = $this->menu_page_url( 'action=setup' );
			} else {
				$sitekey = isset( $_POST['sitekey'] ) ? trim( $_POST['sitekey'] ) : '';
				$secret = isset( $_POST['secret'] ) ? trim( $_POST['secret'] ) : '';

				if ( $sitekey and $secret ) {
					$this->sitekeys = array( $sitekey => $secret );
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
				'<div class="error notice notice-error is-dismissible"><p><strong>%1$s</strong>: %2$s</p></div>',
				esc_html( __( "Error", 'contact-form-7' ) ),
				esc_html( __( "Invalid key values.", 'contact-form-7' ) ) );
		}

		if ( 'success' == $message ) {
			echo sprintf( '<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( __( 'Settings saved.', 'contact-form-7' ) ) );
		}
	}

	public function display( $action = '' ) {
		echo '<p>' . sprintf(
			esc_html( __( 'FriendlyCaptcha is a privacy-friendly, no-cookies system which protects against spam and other types of automated abuse. With Contact Form 7&#8217;s FriendlyCaptcha integration module, you can prevent abusive form submissions by spam bots.', 'contact-form-7' ) ),
			// wpcf7_link(
			// 	__( 'https://contactform7.com/friendly-captcha/', 'contact-form-7' ),
			// 	__( 'Friendly Captcha (v1)', 'contact-form-7' )
			// )
		) . '</p>';

		if ( $this->is_active() ) {
			echo sprintf(
				'<p class="dashicons-before dashicons-yes">%s</p>',
				esc_html( __( "FriendlyCaptcha is active on this site.", 'contact-form-7' ) )
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
		$sitekey = $this->is_active() ? $this->get_sitekey() : '';
		$secret = $this->is_active() ? $this->get_secret( $sitekey ) : '';

?>
<form method="post" action="<?php echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
<?php wp_nonce_field( 'wpcf7-friendly-captcha-setup' ); ?>
<table class="form-table">
<tbody>
<tr>
	<th scope="row"><label for="sitekey"><?php echo esc_html( __( 'SiteKey', 'contact-form-7' ) ); ?></label></th>
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
<tr>
	<th scope="row"><label for="secret"><?php echo esc_html( __( 'API Key', 'contact-form-7' ) ); ?></label></th>
	<td><?php
		if ( $this->is_active() ) {
			echo esc_html( wpcf7_mask_password( $secret ) );
			echo sprintf(
				'<input type="hidden" value="%1$s" id="secret" name="secret" />',
				esc_attr( $secret )
			);
		} else {
			echo sprintf(
				'<input type="text" aria-required="true" value="%1$s" id="secret" name="secret" class="regular-text code" />',
				esc_attr( $secret )
			);
		}
	?></td>
</tr>
</tbody>
</table>
<?php
		if ( $this->is_active() ) {
			if ( $this->get_global_sitekey() && $this->get_global_secret() ) {
				// nothing
			} else {
				submit_button(
					_x( 'Remove Keys', 'API keys', 'contact-form-7' ),
					'small', 'reset'
				);
			}
		} else {
			submit_button( __( 'Save Changes', 'contact-form-7' ) );
		}
?>
</form>
<?php
	}
}
