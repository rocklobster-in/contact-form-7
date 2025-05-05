<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * The WPCF7_HCaptcha class file.
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection AutoloadingIssuesInspection */

if ( ! class_exists( 'WPCF7_Service' ) ) {
	return;
}

/**
 * Class WPCF7_HCaptcha.
 */
class WPCF7_HCaptcha extends WPCF7_Service {
	/**
	 * Field type.
	 */
	public const FIELD_TYPE = 'hcaptcha';

	/**
	 * Instance.
	 *
	 * @var WPCF7_HCaptcha
	 */
	private static WPCF7_HCaptcha $instance;

	/**
	 * Site keys.
	 *
	 * @var array
	 */
	private array $site_keys;

	/**
	 * Whether reCaptcha service is active.
	 *
	 * @var bool
	 */
	private bool $is_recaptcha_active;

	/**
	 * Whether the form was shown.
	 *
	 * @var true
	 */
	private bool $form_shown = false;

	/**
	 * Whether the form has a result.
	 *
	 * @var bool
	 */
	private bool $has_result = false;

	/**
	 * Get instance.
	 *
	 * @return WPCF7_HCaptcha
	 */
	public static function get_instance(): WPCF7_HCaptcha {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * WPCF7_HCaptcha constructor.
	 */
	private function __construct() {
		$this->site_keys           = array_filter( (array) WPCF7::get_option( self::FIELD_TYPE ) );
		$this->is_recaptcha_active = $this->is_recaptcha_active();

		if ( $this->is_recaptcha_active || ! $this->is_active() ) {
			return;
		}

		$this->init_hooks();
	}

	/**
	 * Get title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'hCaptcha', 'contact-form-7' );
	}

	/**
	 * Check whether the service is active.
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		if ( $this->is_recaptcha_active ) {
			return false;
		}

		$site_key   = $this->get_site_key();
		$secret_key = $this->get_secret_key();

		return $site_key && $secret_key;
	}

	/**
	 * Get categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'spam_protection' );
	}

	/**
	 * Print icon.
	 *
	 * @return void
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function icon() {
		?>
		<img
				src="<?php echo esc_url( WPCF7_PLUGIN_URL . '/modules/hcaptcha/hcaptcha-icon-animated.svg' ); ?>"
				style="height: 3.8em; margin-left: 1em;" alt="hCaptcha Logo"/>
		<?php
	}

	/**
	 * Print link.
	 *
	 * @return void
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function link() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo wpcf7_link(
			'https://www.hcaptcha.com',
			'hcaptcha.com'
		);
	}

	/**
	 * Get a global site key.
	 *
	 * @return string
	 */
	public function get_global_site_key(): string {
		static $site_key = '';

		if ( $site_key ) {
			return $site_key;
		}

		if ( defined( 'WPCF7_HCAPTCHA_SITE_KEY' ) ) {
			$site_key = WPCF7_HCAPTCHA_SITE_KEY;
		}

		$site_key = (string) apply_filters( 'wpcf7_hcaptcha_site_key', $site_key );

		return $site_key;
	}

	/**
	 * Get a global secret key.
	 *
	 * @return string
	 */
	public function get_global_secret_key(): string {
		static $secret = '';

		if ( $secret ) {
			return $secret;
		}

		if ( defined( 'WPCF7_HCAPTCHA_SECRET' ) ) {
			$secret = WPCF7_HCAPTCHA_SECRET;
		}

		$secret = (string) apply_filters( 'wpcf7_hcaptcha_secret_key', $secret );

		return $secret;
	}

	/**
	 * Get a site key.
	 *
	 * @return false|int|string
	 */
	public function get_site_key() {
		if ( $this->get_global_site_key() && $this->get_global_secret_key() ) {
			return $this->get_global_site_key();
		}

		return empty( $this->site_keys['site_key'] ) ? false : $this->site_keys['site_key'];
	}

	/**
	 * Get secret key.
	 *
	 * @return false|mixed|string
	 */
	public function get_secret_key() {
		if ( $this->get_global_site_key() && $this->get_global_secret_key() ) {
			return $this->get_global_secret_key();
		}

		return empty( $this->site_keys['secret_key'] ) ? false : $this->site_keys['secret_key'];
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function init_hooks(): void {
		add_filter( 'do_shortcode_tag', array( $this, 'wpcf7_shortcode' ), 20, 4 );
		add_action( 'wp_head', array( $this, 'print_inline_styles' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'print_footer_scripts' ), 0 );
	}

	/**
	 * Add hCaptcha to CF7 form.
	 *
	 * @param string|mixed $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function wpcf7_shortcode( $output, string $tag, $attr, array $m ): string {
		$output = (string) $output;

		if ( 'contact-form-7' !== $tag ) {
			return $output;
		}

		if ( $this->has_stripe_element( $output ) ) {
			/**
			 * Do not show hCaptcha in the CF7 form having Stripe field.
			 * Stripe payment form has its own hidden hCaptcha field.
			 */
			return $output;
		}

		$args = array(
			'action' => 'wp_rest',
			'name'   => '_wpnonce',
		);

		$cf7_hcap_form = $this->form( $args );
		$submit_button = '/(<(input|button) .*?type="submit")/';

		return preg_replace(
			$submit_button,
			$cf7_hcap_form . '$1',
			$output
		);
	}

	/**
	 * Print inline styles.
	 *
	 * @return void
	 * @noinspection CssUnusedSymbol
	 * @noinspection CssUnknownTarget
	 */
	public function print_inline_styles(): void {
		/* language=PHP */
		$div_logo_url = WPCF7_PLUGIN_URL . '/modules/hcaptcha/hcaptcha-div-logo.svg';

		/* language=CSS */
		$css = '
	.wpcf7 form .h-captcha {
		position: relative;
		display: block;
		margin-bottom: 2rem;
		padding: 0;
		clear: both;
	}

	.wpcf7 form .h-captcha[data-size="normal"] {
		width: 303px;
		height: 78px;
	}

	.wpcf7 form .h-captcha::before {
		content: \'\';
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		background: url( ' . esc_url( $div_logo_url ) . ' ) no-repeat;
		border: 1px solid transparent;
		border-radius: 4px;
	}

	.wpcf7 form .h-captcha[data-size="normal"]::before {
		width: 300px;
		height: 74px;
		background-position: 94% 28%;
		background-color: #fafafa;
		border: 1px solid #e0e0e0;
	}

	.wpcf7 form .h-captcha iframe {
		position: relative;
	}

	div[style*="z-index: 2147483647"] div[style*="border-width: 11px"][style*="position: absolute"][style*="pointer-events: none"] {
		border-style: none;
	}
';

		/* language=PHP */

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "<style>$css</style>";
	}

	/**
	 * Add the hCaptcha script to footer.
	 *
	 * @return void
	 */
	public function print_footer_scripts(): void {
		if ( ! $this->form_shown ) {
			return;
		}

		wp_enqueue_script(
			'hcaptcha-api',
			'https://js.hcaptcha.com/1/api.js',
			array(),
			WPCF7_VERSION,
			true
		);
	}

	/**
	 * Verify hCaptcha.
	 *
	 * @param WPCF7_Submission $submission Submission.
	 *
	 * @return string|null
	 */
	public function verify( WPCF7_Submission $submission ): ?string {
		if ( $this->has_field( $submission, 'stripe' ) ) {
			/**
			 * Do not verify CF7 form having Stripe field.
			 * Stripe payment form has its own hidden hCaptcha field.
			 */
			return null;
		}

		$data     = $submission->get_posted_data();
		$response = $data['h-captcha-response'] ?? '';

		return $this->request_verify( $response );
	}

	/**
	 * Verify hCaptcha response.
	 *
	 * @param string|null $hcaptcha_response hCaptcha response.
	 *
	 * @return null|string Null on success, error message on failure.
	 * @noinspection PhpMissingParamTypeInspection
	 * @noinspection PhpTernaryExpressionCanBeReducedToShortVersionInspection
	 * @noinspection ElvisOperatorCanBeUsedInspection
	 */
	private function request_verify( $hcaptcha_response ): ?string {
		static $result;

		// Do not make remote request more than once.
		if ( $this->has_result ) {
			/**
			 * Filters the result of request verification.
			 *
			 * @param string|null $result      The result of verification. The null means success.
			 * @param string[]    $error_codes Error code(s). Empty array on success.
			 */
			return $result;
		}

		$this->has_result = true;

		$errors        = $this->get_hcaptcha_error_messages();
		$empty_message = $errors['empty'];
		$fail_message  = $errors['fail'];

		$hcaptcha_response_sanitized = htmlspecialchars(
			filter_var( $hcaptcha_response, FILTER_SANITIZE_FULL_SPECIAL_CHARS ),
			ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
		);

		// The hCaptcha response field is empty.
		if ( '' === $hcaptcha_response_sanitized ) {
			$result = $empty_message;

			return $result;
		}

		$params = array(
			'secret'   => $this->get_secret_key(),
			'response' => $hcaptcha_response_sanitized,
		);

		// Verify hCaptcha on the API server.
		$raw_response = wp_remote_post(
			'https://api.hcaptcha.com/siteverify',
			array( 'body' => $params )
		);

		if ( is_wp_error( $raw_response ) ) {
			$result = implode( "\n", $raw_response->get_error_messages() );

			return $result;
		}

		$raw_body = wp_remote_retrieve_body( $raw_response );

		// Verification request failed.
		if ( empty( $raw_body ) ) {
			$result = $fail_message;

			return $result;
		}

		try {
			$body = json_decode( $raw_body, true, 512, JSON_THROW_ON_ERROR );
		} catch ( JsonException $e ) {
			$body = array();
		}

		// Verification request is not verified.
		if ( ! isset( $body['success'] ) || true !== (bool) $body['success'] ) {
			$error_codes        = $body['error-codes'] ?? array();
			$hcap_error_message = $this->get_hcaptcha_error_message( $error_codes );
			$result             = $hcap_error_message ? $hcap_error_message : $fail_message;

			return $result;
		}

		// Success.
		return null;
	}

	/**
	 * Get error messages provided by API and the plugin.
	 *
	 * @return array
	 */
	private function get_hcaptcha_error_messages(): array {
		return array(
			// API messages.
			'missing-input-secret'     => __( 'Your secret key is missing.', 'contact-form-7' ),
			'invalid-input-secret'     => __( 'Your secret key is invalid or malformed.', 'contact-form-7' ),
			'missing-input-response'   => __( 'The response parameter (verification token) is missing.', 'contact-form-7' ),
			'invalid-input-response'   => __( 'The response parameter (verification token) is invalid or malformed.', 'contact-form-7' ),
			'expired-input-response'   => __( 'The response parameter (verification token) is expired. (120s default)', 'contact-form-7' ),
			'already-seen-response'    => __( 'The response parameter (verification token) was already verified once.', 'contact-form-7' ),
			'bad-request'              => __( 'The request is invalid or malformed.', 'contact-form-7' ),
			'missing-remoteip'         => __( 'The remoteip parameter is missing.', 'contact-form-7' ),
			'invalid-remoteip'         => __( 'The remoteip parameter is not a valid IP address or blinded value.', 'contact-form-7' ),
			'not-using-dummy-passcode' => __( 'You have used a testing sitekey but have not used its matching secret.', 'contact-form-7' ),
			'sitekey-secret-mismatch'  => __( 'The sitekey is not registered with the provided secret.', 'contact-form-7' ),
			// Plugin messages.
			'empty'                    => __( 'Please complete the hCaptcha.', 'contact-form-7' ),
			'fail'                     => __( 'The hCaptcha is invalid.', 'contact-form-7' ),
			'bad-nonce'                => __( 'Bad hCaptcha nonce!', 'contact-form-7' ),
			'bad-signature'            => __( 'Bad hCaptcha signature!', 'contact-form-7' ),
		);
	}

	/**
	 * Get hCaptcha error message.
	 *
	 * @param string|string[] $error_codes Error codes.
	 *
	 * @return string
	 */
	private function get_hcaptcha_error_message( $error_codes ): string {
		$error_codes = (array) $error_codes;
		$errors      = $this->get_hcaptcha_error_messages();
		$message_arr = array();

		foreach ( $error_codes as $error_code ) {
			if ( array_key_exists( $error_code, $errors ) ) {
				$message_arr[] = $errors[ $error_code ];
			}
		}

		if ( ! $message_arr ) {
			return '';
		}

		$header = _n( 'hCaptcha error:', 'hCaptcha errors:', count( $message_arr ), 'contact-form-7' );

		return $header . ' ' . implode( '; ', $message_arr );
	}

	/**
	 * Menu page URL.
	 *
	 * @param string|string[] $args Arguments.
	 *
	 * @return string
	 */
	protected function menu_page_url( $args = '' ): string {
		$args = wp_parse_args( $args );

		$url = menu_page_url( 'wpcf7-integration', false );
		$url = add_query_arg( array( 'service' => self::FIELD_TYPE ), $url );

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * Save data.
	 *
	 * @return void
	 */
	protected function save_data(): void {
		WPCF7::update_option( self::FIELD_TYPE, $this->site_keys );
	}

	/**
	 * Reset data.
	 *
	 * @return void
	 */
	protected function reset_data(): void {
		$this->site_keys = array();

		$this->save_data();
	}

	/**
	 * Load.
	 *
	 * @param string|mixed $action Action.
	 *
	 * @return void
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function load( $action = '' ) {
		$request_method = isset( $_SERVER['REQUEST_METHOD'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
			: '';

		if ( 'setup' !== $action || 'POST' !== strtoupper( $request_method ) ) {
			return;
		}

		check_admin_referer( 'wpcf7-hcaptcha-setup' );

		if ( ! empty( $_POST['reset'] ) ) {
			$this->reset_data();
			$redirect_to = $this->menu_page_url( 'action=setup' );
		} else {
			$site_key   = isset( $_POST['site_key'] )
				? trim( sanitize_text_field( wp_unslash( $_POST['site_key'] ) ) )
				: '';
			$secret_key = isset( $_POST['secret_key'] )
				? trim( sanitize_text_field( wp_unslash( $_POST['secret_key'] ) ) )
				: '';

			if ( $site_key && $secret_key ) {
				$this->site_keys = array(
					'site_key'   => $site_key,
					'secret_key' => $secret_key,
				);

				$this->save_data();

				$redirect_to = $this->menu_page_url(
					array(
						'message' => 'success',
					)
				);
			} else {
				$redirect_to = $this->menu_page_url(
					array(
						'action'  => 'setup',
						'message' => 'invalid',
					)
				);
			}
		}

		wp_safe_redirect( $redirect_to );

		exit();
	}

	/**
	 * Admin notice.
	 *
	 * @param string|mixed $message Message.
	 *
	 * @return void
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 */
	public function admin_notice( $message = '' ) {
		if ( 'invalid' === $message ) {
			wp_admin_notice(
				sprintf(
					'<strong>%1$s</strong>: %2$s',
					esc_html( __( 'Error', 'contact-form-7' ) ),
					esc_html( __( 'Invalid key values.', 'contact-form-7' ) )
				),
				array( 'type' => 'error' )
			);
		}

		if ( 'success' === $message ) {
			wp_admin_notice(
				esc_html( __( 'Settings saved.', 'contact-form-7' ) ),
				array( 'type' => 'success' )
			);
		}
	}

	/**
	 * Display.
	 *
	 * @param string|mixed $action Action.
	 *
	 * @return void
	 * @noinspection ReturnTypeCanBeDeclaredInspection
	 * @noinspection HtmlUnknownTarget
	 */
	public function display( $action = '' ) {
		$out = sprintf(
			'<p>%s</p>',
			esc_html( __( "hCaptcha is designed to protect user privacy. It doesn't retain or sell personal data. With Contact Form 7's hCaptcha integration module, you get better protection against bots and abuse than other anti-abuse systems.", 'contact-form-7' ) )
		);

		$out .= sprintf(
			'<p><strong>%s</strong></p>',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wpcf7_link(
				__( 'https://www.hcaptcha.com', 'contact-form-7' ),
				__( 'hCaptcha', 'contact-form-7' )
			)
		);

		if ( $this->is_recaptcha_active ) {
			$out .= sprintf(
				'<p class="dashicons-before dashicons-no" style="color: #d63638;">%s</p>',
				esc_html( __( 'Please deactivate reCaptcha service to use hCaptcha.', 'contact-form-7' ) )
			);
		} elseif ( $this->is_active() ) {
			$out .= sprintf(
				'<p class="dashicons-before dashicons-yes">%s</p>',
				esc_html( __( 'hCaptcha is active on this site.', 'contact-form-7' ) )
			);
		}

		echo wp_kses_post( apply_filters( 'wpcf7_hcaptcha_display', $out ) );

		if ( 'setup' === $action ) {
			$this->display_setup();
		} elseif ( ! $this->is_recaptcha_active ) {
			printf(
				'<p><a href="%1$s" class="button">%2$s</a></p>',
				esc_url( $this->menu_page_url( 'action=setup' ) ),
				esc_html( __( 'Setup Integration', 'contact-form-7' ) )
			);
		}
	}

	/**
	 * Display setup.
	 *
	 * @return void
	 */
	private function display_setup(): void {
		$site_key   = $this->is_active() ? $this->get_site_key() : '';
		$secret_key = $this->is_active() ? $this->get_secret_key() : '';

		/* language=HTML */

		?>
		<form method="post" action="<?php echo esc_url( $this->menu_page_url( 'action=setup' ) ); ?>">
			<?php wp_nonce_field( 'wpcf7-hcaptcha-setup' ); ?>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<label for="site_key">
							<?php echo esc_html( __( 'Site Key', 'contact-form-7' ) ); ?>
						</label></th>
					<td>
						<?php

						if ( $this->is_active() ) {
							echo esc_html( $site_key );
							printf(
								'<input type="hidden" value="%1$s" id="site_key" name="site_key" />',
								esc_attr( $site_key )
							);
						} else {
							printf(
								'<input type="text" aria-required="true" value="%1$s" id="site_key" name="site_key" class="regular-text code" />',
								esc_attr( $site_key )
							);
						}

						?>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="secret_key">
							<?php echo esc_html( __( 'Secret Key', 'contact-form-7' ) ); ?>
						</label>
					</th>
					<td>
						<?php

						if ( $this->is_active() ) {
							echo esc_html( wpcf7_mask_password( $secret_key, 4, 4 ) );
							printf(
								'<input type="hidden" value="%1$s" id="secret_key" name="secret_key" />',
								esc_attr( $secret_key )
							);
						} else {
							printf(
								'<input type="text" aria-required="true" value="%1$s" id="secret_key" name="secret_key" class="regular-text code" />',
								esc_attr( $secret_key )
							);
						}

						?>
					</td>
				</tr>
				</tbody>
			</table>
			<?php

			if ( $this->is_active() ) {
				if ( ! ( $this->get_global_site_key() && $this->get_global_secret_key() ) ) {
					submit_button(
						_x( 'Remove Keys', 'API keys', 'contact-form-7' ),
						'small',
						'reset'
					);
				}
			} else {
				submit_button( __( 'Save Changes', 'contact-form-7' ) );
			}

			?>
		</form>
		<?php

		/* language=PHP */
	}

	/**
	 * Whether the form contains a Stripe element.
	 *
	 * @param string $output Output.
	 *
	 * @return bool
	 */
	private function has_stripe_element( string $output ): bool {
		return false !== strpos( $output, '<div class="wpcf7-stripe">' );
	}

	/**
	 * Get hCaptcha form.
	 *
	 * @param array $args Arguments.
	 *
	 * @return string
	 */
	private function form( array $args = array() ): string {
		ob_start();
		$this->form_display( $args );

		return (string) ob_get_clean();
	}

	/**
	 * Display hCaptcha form.
	 *
	 * @param array $args Arguments.
	 */
	private function form_display( array $args = array() ): void {
		$hcaptcha_site_key = $this->get_site_key();

		$args = wp_parse_args(
			$args,
			array(
				'action' => '', // Action name for wp_nonce_field.
				'name'   => '', // Nonce name for wp_nonce_field.
			)
		);

		/**
		 * Filters the hCaptcha form arguments.
		 *
		 * @param array $args The hCaptcha form arguments.
		 */
		$args = (array) apply_filters( 'hcap_form_args', $args );

		$args['action'] = (string) $args['action'];
		$args['name']   = (string) $args['name'];

		$this->form_shown = true;

		?>
		<h-captcha
				class="h-captcha"
				data-sitekey="<?php echo esc_attr( $hcaptcha_site_key ); ?>">
		</h-captcha>
		<?php

		if ( ! empty( $args['action'] ) && ! empty( $args['name'] ) ) {
			wp_nonce_field( $args['action'], $args['name'] );
		}
	}

	/**
	 * Whether form has a field of given type.
	 *
	 * @param WPCF7_Submission $submission Submission.
	 * @param string           $type       Field type.
	 *
	 * @return bool
	 * @noinspection PhpSameParameterValueInspection
	 */
	private function has_field( WPCF7_Submission $submission, string $type ): bool {
		$has_field    = false;
		$contact_form = $submission->get_contact_form();
		$form_fields  = $contact_form->scan_form_tags();

		foreach ( $form_fields as $form_field ) {
			if ( $type === $form_field->type ) {
				$has_field = true;

				break;
			}
		}

		return $has_field;
	}

	/**
	 * Whether reCaptcha is active or not.
	 *
	 * @return bool
	 */
	private function is_recaptcha_active(): bool {
		$global_site_key = defined( 'WPCF7_RECAPTCHA_SITEKEY' ) ? WPCF7_RECAPTCHA_SITEKEY : '';
		$global_site_key = apply_filters( 'wpcf7_recaptcha_sitekey', $global_site_key );

		$global_secret_key = defined( 'WPCF7_RECAPTCHA_SECRET' ) ? WPCF7_RECAPTCHA_SECRET : '';
		$global_secret_key = apply_filters( 'wpcf7_recaptcha_secret', $global_secret_key );

		if ( $global_site_key && $global_secret_key ) {
			return true;
		}

		$option    = array_filter( (array) get_option( 'wpcf7' ) );
		$recaptcha = $option['recaptcha'] ?? array();

		$site_key   = array_keys( $recaptcha )[0] ?? '';
		$secret_key = $recaptcha[ $site_key ] ?? '';

		return $site_key && $secret_key;
	}
}
