<?php

if ( ! class_exists('WPCF7_Service') ) {
	return;
}

class WPCF7_CLEANTALK extends WPCF7_Service
{

	private static $instance;
	private $apikey;

	public static function get_instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->apikey = WPCF7::get_option( 'cleantalk_api_key' );
	}

	public function get_title() {
		return esc_html__( 'CleanTalk Anti-Spam', 'contact-form-7' );
	}

	public function is_active() {
		return $this->get_apikey();
	}

	public function get_categories() {
		return array( 'spam_protection' );
	}

	public function icon() {
		echo sprintf(
				'<img src="%s" alt="CleanTalk Anti-Spam service icon" width="28" height="28" style="float:left;margin:1em 1em 1em 0;" />',
				esc_attr('https://ps.w.org/cleantalk-spam-protect/assets/icon-128x128.png')
		);
	}

	public function link() {
		echo wpcf7_link(
				'https://cleantalk.org',
				'cleantalk.org'
		);
	}

	public function get_apikey() {
		return $this->apikey;
	}

	protected function log( $url, $request, $response ) {
		wpcf7_log_remote_request( $url, $request, $response );
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
		WPCF7::update_option( 'cleantalk_api_key', $this->apikey );
	}

	protected function reset_data() {
		$this->apikey = null;
		$this->save_data();
	}

	public function load( $action = '' ) {
		if ( 'setup' == $action and 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'wpcf7-cleantalk-setup' );

			if ( ! empty( $_POST['reset'] ) ) {
				$this->reset_data();
				$redirect_to = $this->menu_page_url( 'action=setup' );
			} else {
				$apikey = isset( $_POST['apikey'] ) ? trim( $_POST['apikey'] ) : '';

				$apikey = $this->account_status_check( $apikey );

				if ( $apikey ) {
					$this->apikey = $apikey;
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

			if ( WPCF7::get_option( 'cleantalk_warning' ) ) {
				WPCF7::update_option( 'cleantalk_warning', false );
			}

			wp_safe_redirect( $redirect_to );
			exit();
		}
	}

	public function admin_notice( $message = '' ) {
		if ( 'invalid' == $message ) {
			echo sprintf(
					'<div class="notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
					esc_html( __( "Error", 'contact-form-7' ) ),
					esc_html( __( "Invalid Access Key value.", 'contact-form-7' ) ) );
		}

		if ( 'success' == $message ) {
			echo sprintf( '<div class="notice notice-success"><p>%s</p></div>',
					esc_html( __( 'Settings saved.', 'contact-form-7' ) ) );
		}
	}

	public function display( $action = '' ) {
		echo '<p>' . sprintf(
						esc_html( __( 'No CAPTCHA, no questions, no animal counting, no puzzles, no math and no spam bots. Universal AntiSpam plugin. For details, see %s.',
								'contact-form-7' ) ),
						wpcf7_link(
								'https://cleantalk.org/',
								__( 'CleanTalk Anti-Spam', 'contact-form-7' )
						)
				) . '</p>';

		if ( $this->is_active() ) {
			echo sprintf(
					'<p class="dashicons-before dashicons-yes">%s</p>',
					esc_html( __( "CleanTalk Anti-Spam is active on this site.", 'contact-form-7' ) )
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
		$sitekey = $this->is_active() ? $this->get_apikey() : '';

		?>
		<form method="post" action="<?php
		echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
			<?php
			wp_nonce_field( 'wpcf7-cleantalk-setup' ); ?>
			<table class="form-table">
				<tbody>
				<?php if ( ! $this->is_active() ) : ?>
				<tr>
					<th scope="row"></th>
					<td>
						<?php echo $this->get_key_link(); ?>
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<th scope="row"><label for="apikey"><?php
							echo esc_html( __( 'Access Key', 'contact-form-7' ) ); ?></label></th>
					<td><?php
						if ( $this->is_active() ) {
							echo esc_html( $sitekey );
							echo sprintf(
									'<input type="hidden" value="%1$s" id="apikey" name="apikey" />',
									esc_attr( $sitekey )
							);
						} else {
							echo sprintf(
									'<input type="text" aria-required="true" value="%1$s" id="apikey" name="apikey" class="regular-text code" />',
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
						_x( 'Remove Key', 'Access Key', 'contact-form-7' ),
						'small',
						'reset'
				);
			} else {
				submit_button( __( 'Save Changes', 'contact-form-7' ) );
			}
			?>
		</form>
		<?php
	}

	private function get_key_link() {
		$register_url = sprintf(
				'https://cleantalk.org/register?platform=wordpress&email=%s&website=%s&utm_source=%s',
				get_option('admin_email'),
				parse_url(get_option('home'), PHP_URL_HOST) . parse_url(get_option('home'), PHP_URL_PATH),
				'cf7_direct_plugin_integration'
		);
		return sprintf(
			esc_html__('Click %s to get the Access Key', 'contact-form-7'),
			'<a href="' . $register_url . '" target="_blank">' . esc_html__('here', 'contact-form-7') . '</a>'
		);
	}

	private function account_status_check( $apikey ) {
		$endpoint = 'https://api.cleantalk.org';

		$request = array(
			'body' => array(
				'method_name' => 'notice_paid_till',
				'path_to_cms' => preg_replace('/http[s]?:\/\//', '', get_option('home'), 1),
				'auth_key'    => $apikey,
				'product_id'  => 1
			),
		);

		$result = wp_remote_post( esc_url_raw( $endpoint ), $request );
		$result = json_decode($result['body'], true);
		$result = $result['data'];

		if ( empty($result['error']) && ! empty($result['valid']) ) {
			return $apikey;
		}

		return false;
	}
}
