<?php
/**
 * The hCaptcha module main file.
 *
 * @link https://contactform7.com/hcaptcha/
 * @package contact-form-7
 */

wpcf7_include_module_file( 'hcaptcha/service.php' );

add_action( 'wpcf7_init', 'wpcf7_hcaptcha_register_service', 30, 0 );

/**
 * Registers the hCaptcha service.
 */
function wpcf7_hcaptcha_register_service(): void {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_service( 'hcaptcha', WPCF7_HCaptcha::get_instance() );
}

add_filter( 'wpcf7_spam', 'wpcf7_hcaptcha_verify_response', 9, 2 );

/**
 * Verifies reCAPTCHA token on the server side.
 *
 * @param bool|mixed       $spam       Whether submission is spam or not.
 * @param WPCF7_Submission $submission Submission.
 *
 * @return bool
 */
function wpcf7_hcaptcha_verify_response( $spam, WPCF7_Submission $submission ): bool {
	$spam = (bool) $spam;

	if ( $spam ) {
		return true;
	}

	$service = WPCF7_HCaptcha::get_instance();

	if ( ! $service->is_active() ) {
		return false;
	}

	$result = $service->verify( $submission );

	if ( null === $result ) {
		return false;
	}

	$submission->add_spam_log(
		array(
			'agent'  => 'hcaptcha',
			'reason' => $result,
		)
	);

	return true;
}

add_action( 'wpcf7_init', 'wpcf7_hcaptcha_add_form_tag_hcaptcha', 10, 0 );

/**
 * Registers form-tag types for hCaptcha.
 */
function wpcf7_hcaptcha_add_form_tag_hcaptcha() {
	$service = WPCF7_HCaptcha::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	wpcf7_add_form_tag(
		'hcaptcha',
		static function () {
			return '';  // No output.
		},
		array( 'display-block' => true )
	);
}
