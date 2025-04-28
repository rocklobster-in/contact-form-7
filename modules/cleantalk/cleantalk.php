<?php
/**
 * CleanTalk module main file
 *
 * @link https://cleantalk.org
 */

wpcf7_include_module_file( 'cleantalk/service.php' );


add_action( 'wpcf7_init', 'wpcf7_cleantalk_register_service', 40, 0 );

/**
 * Registers the CleanTalk service.
 */
function wpcf7_cleantalk_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_service( 'cleantalk',
		WPCF7_CleanTalk::get_instance()
	);
}


add_action(
	'wp_enqueue_scripts',
	'wpcf7_cleantalk_enqueue_scripts',
	20, 0
);

/**
 * Enqueues frontend scripts for CleanTalk.
 */
function wpcf7_cleantalk_enqueue_scripts() {
	$service = WPCF7_CleanTalk::get_instance();

	if ( ! $service->is_active() || \CleanTalkCF7SDK\CleanTalkSDK::isCleantalkPluginActive() ) {
		return;
	}

	\CleanTalkCF7SDK\CleanTalkSDK::wpEnqueuePublicScriptBotDetector();
}

add_filter( 'wpcf7_spam', 'wpcf7_cleantalk_verify_response', 9, 2 );

/**
 * Check for spam with CleanTalk service.
 */
function wpcf7_cleantalk_verify_response( $spam, $submission ) {
	if ( $spam ) {
		return $spam;
	}

	$service = WPCF7_CleanTalk::get_instance();

	if ( ! $service->is_active() ) {
		return $spam;
	}

	$cleantalk_is_spam = $service->verify();

	if ( '' === $service->cleantalk_sdk->cleantalk_message->event_token ) {
		$submission->add_spam_log(array(
			                          'agent'  => 'cleantalk',
			                          'reason' => __(
				                          'CleanTalk event token field is empty. Probably user has no JavaScript enabled.',
				                          'contact-form-7'
			                          ),
		                          ));
	}

	if ( $cleantalk_is_spam ) { // Human
		$spam = false;
	} else { // Bot
		$spam = true;
		$submission->add_spam_log(array(
			                          'agent'  => 'cleantalk',
			                          'reason' => sprintf(
				                          __(
					                          'CleanTalk detected spam, server comment: %1$s',
					                          'contact-form-7'
				                          ),
				                          $service->get_cleantalk_server_comment()
			                          ),
		                          ));
	}

	return $spam;
}

add_action( 'wpcf7_admin_menu', 'wpcf7_admin_init_cleantalk', 10, 0 );

/**
 * Adds filters and actions for warnings.
 */
function wpcf7_admin_init_cleantalk() {
	if ( ! WPCF7::get_option( 'cleantalk_warning' ) ) {
		return;
	}

	add_filter(
		'wpcf7_admin_menu_change_notice',
		'wpcf7_admin_menu_change_notice_cleantalk',
		10, 1
	);

	add_action(
		'wpcf7_admin_warnings',
		'wpcf7_admin_warnings_cleantalk',
		5, 3
	);
}


/**
 * Increments the admin menu counter for the Integration page.
 */
function wpcf7_admin_menu_change_notice_cleantalk( $counts ) {
	$counts['wpcf7-integration'] += 1;
	return $counts;
}


/**
 * Prints warnings on the admin screen.
 */
function wpcf7_admin_warnings_cleantalk( $page, $action, $object ) {
	if ( 'wpcf7-integration' !== $page ) {
		return;
	}

	$service = WPCF7_CleanTalk::get_instance();

	if ( !$service->is_active() ) {
		$message = sprintf(
			esc_html(
				__(
					"CleanTalk access key is invalid or expired. Please, visit your %s to get a valid key ",
					'contact-form-7'
				)
			),
			wpcf7_link(
				$service->cleantalk_sdk::getCleanTalkUTMLink($service->vendor_agent,'my'),
				__( 'CleanTalk Dashboard', 'contact-form-7' )
			)
		);

		wp_admin_notice( $message, array( 'type' => 'warning' ) );
	}
}
