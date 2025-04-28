<?php

if ( ! class_exists( 'WPCF7_Service' ) ) {
	return;
}

require_once WPCF7_PLUGIN_DIR . '/modules/cleantalk/cleantalk_wordpress_sdk.php';

class WPCF7_CleanTalk extends WPCF7_Service {

	private static $instance;
	private $access_key;
	private $key_verified = false;
	/**
	 * @var CleanTalkCF7SDK\CleanTalkSDK
	 */
	public $cleantalk_sdk;
	public $vendor_agent = 'contactform7';
	public $vendor_version = WPCF7_VERSION;

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->access_key = WPCF7::get_option('cleantalk');
		$this->cleantalk_sdk = new CleanTalkCF7SDK\CleanTalkSDK(
			$this->vendor_agent,
			$this->vendor_version,
			null,
			false
		);
	}


	public function get_title() {
		return __( 'CleanTalk', 'contact-form-7' );
	}


	public function is_active() {
		$sitekey = $this->get_access_key();
		return $sitekey;
	}


	public function get_categories() {
		return array( 'spam_protection' );
	}


	public function icon() {
	}


	public function link() {
		echo wpcf7_link(
			$this->cleantalk_sdk::getCleanTalkUTMLink($this->vendor_agent, 'my'),
			'CleanTalk Dashboard'
		);
	}


	public function get_access_key() {
		if ( empty( $this->access_key ) ) {
			return false;
		}

		return $this->access_key;
	}


	public function verify( ) {
		$is_human = false;

		$this->cleantalk_sdk->setAccessKey($this->get_access_key(), false);

		$form_data = WPCF7_Submission::get_instance()->get_posted_data();

		$custom_sdk_message = $this->cleantalk_sdk->getDefaultHTTPMessage($form_data, $this->get_access_key());

		$custom_sdk_message->message = !empty($form_data['your-message'])
			? (!is_scalar($form_data['your-message'])
				? serialize($form_data['your-message'])
				: $form_data['your-message'])
			: null;
		$cleantalk_response = $this->cleantalk_sdk->getCleanTalkResponse($custom_sdk_message);

		if (1 == $cleantalk_response->allow) {
			$is_human = true;
		} else {
			add_filter('wpcf7_display_message', array($this, 'filter_display_message'), 20, 1);
		}

		if ( $submission = WPCF7_Submission::get_instance() ) {
			$submission->push( 'cleantalk', array(
				'version' => CleanTalkCF7SDK\CleanTalkSDK::SDK_VERSION,
				'comment' => $this->cleantalk_sdk->cleantalk_response->comment
			) );
		}

		return $is_human;
	}

	public function filter_display_message($message, $status = 'spam') {
		if ($status === 'spam') {
			$message = $this->cleantalk_sdk->cleantalk_response->comment;
		}
		return $message;
	}


	public function get_cleantalk_server_comment() {
		return $this->cleantalk_sdk->cleantalk_response->comment;
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
				$input_site_key = trim(isset($_POST['sitekey']) ? $_POST['sitekey'] : '');
				$this->validate_access_key($input_site_key);
				$input_site_key = $this->key_verified ? $input_site_key : '';
				if ( $input_site_key ) {
					$this->access_key = $input_site_key;
					$this->save_data();
					$redirect_to = $this->menu_page_url(
						array(
							'message' => 'success',
						)
					);
				} else {
					$redirect_to = $this->menu_page_url( array(
						'action' => 'setup',
						'message' => 'invalid',
					) );
					WPCF7::update_option('cleantalk_warning', 'CUSTOM');
				}
			}

			if ( $this->key_verified && WPCF7::get_option('cleantalk_warning' ) ) {
				WPCF7::update_option( 'cleantalk_warning', false );
			}

			wp_safe_redirect( $redirect_to );
			exit();
		}
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
			esc_html( __( "CleanTalk eliminates the need for CAPTCHA, questions&answers and other ways which use complicated communication methods for spam protection on your site. Invisible to the visitors, spam protection has a positive effect on the loyalty of the site's audience.", 'contact-form-7' ) )
		);

		if ( $this->is_active() ) {
			echo sprintf(
				'<p class="dashicons-before dashicons-yes">%s</p>',
				esc_html( __( "CleanTalk is active on this site.", 'contact-form-7' ) )
			);
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

	private function validate_access_key($access_key)
	{
		$validation_result  = $this->cleantalk_sdk->sync($access_key, true);
		$this->key_verified = !empty($validation_result['success']);
		if (!$this->key_verified) {
			WPCF7_CleanTalk::get_instance()->reset_data();
		}
	}


	private function display_setup() {
		$sitekey = $this->get_access_key();
?>
<form method="post" action="<?php echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
<?php wp_nonce_field( 'wpcf7-cleantalk-setup' ); ?>
<table class="form-table">
<tbody>
<tr>
	<th scope="row"><label for="sitekey"><?php echo esc_html( __( 'CleanTalk Access Key', 'contact-form-7' ) ); ?></label></th>
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
