<?php
/**
 * CleanTalk module
 *
 * @link https://cleantalk.org
 */

wpcf7_include_module_file( 'cleantalk/service.php' );


add_action( 'wpcf7_init', 'wpcf7_cleantalk_register_service', 40, 0 );

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

function wpcf7_cleantalk_enqueue_scripts() {
	$service = WPCF7_CleanTalk::get_instance();

	if ( ! $service->is_active() || defined( 'APBCT_VERSION' ) ) {
		return;
	}

	wp_enqueue_script(
		'cleantalk-bot-detector',
		WPCF7_CleanTalk::BOT_DETECTOR_URL,
		array(),
		WPCF7_CleanTalk::SDK_VERSION,
		array( 'in_footer' => false, 'strategy' => 'defer' )
	);
}


add_filter( 'wpcf7_spam', 'wpcf7_cleantalk_verify_response', 9, 2 );

function wpcf7_cleantalk_verify_response( $spam, $submission ) {
	if ( $spam ) {
		return $spam;
	}

	$service = WPCF7_CleanTalk::get_instance();

	if ( ! $service->is_active() ) {
		return $spam;
	}

	$result = $service->verify();

	if ( $result['allow'] ) {
		return false;
	}

	$submission->add_spam_log( array(
		'agent'  => 'cleantalk',
		'reason' => sprintf(
			__( 'CleanTalk: %s', 'contact-form-7' ),
			$result['comment']
		),
	) );

	$comment = $result['comment'];

	add_filter( 'wpcf7_display_message', function( $message, $status ) use ( $comment ) {
		if ( 'spam' === $status && $comment ) {
			return $comment;
		}
		return $message;
	}, 20, 2 );

	return true;
}


add_action( 'wpcf7_admin_menu', 'wpcf7_admin_init_cleantalk', 10, 0 );

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


function wpcf7_admin_menu_change_notice_cleantalk( $counts ) {
	$counts['wpcf7-integration'] += 1;
	return $counts;
}


function wpcf7_admin_warnings_cleantalk( $page, $action, $object ) {
	if ( 'wpcf7-integration' !== $page ) {
		return;
	}

	$service = WPCF7_CleanTalk::get_instance();

	if ( ! $service->is_active() ) {
		$message = sprintf(
			esc_html(
				__(
					"Access key is not configured. %s to get a key.",
					'contact-form-7'
				)
			),
			wpcf7_link(
				'https://cleantalk.org/register',
				__( 'Sign up', 'contact-form-7' )
			)
		);

		wp_admin_notice( $message, array( 'type' => 'warning' ) );
	}
}