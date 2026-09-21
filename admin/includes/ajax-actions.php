<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action(
	'wp_ajax_wpcf7-dashboard-widgets',
	'wpcf7_ajax_dashboard_widgets',
	10, 0
);

/**
 * Ajax action to update the Contact Form 7 news in the Dashboard screen.
 */
function wpcf7_ajax_dashboard_widgets() {
	require_once WPCF7_PLUGIN_DIR . '/admin/includes/dashboard.php';

	set_current_screen( 'toplevel_page_wpcf7-dashboard' );

	if ( 'dashboard_primary' === wpcf7_superglobal_get( 'widget' ) ) {
		wpcf7_dashboard_cf7com_news();
	}

	wp_die();
}


add_action(
	'wp_ajax_wpcf7-sendinblue-contact-lists',
	'wpcf7_ajax_sendinblue_contact_lists',
	10, 0
);

/**
 * Ajax action to update the contact lists in the Brevo editor panel.
 */
function wpcf7_ajax_sendinblue_contact_lists() {
	$contact_form = wpcf7_contact_form( wpcf7_superglobal_get( 'id' ) );

	$prop = wp_parse_args(
		$contact_form ? $contact_form->prop( 'sendinblue' ) : array(),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

	wpcf7_sendinblue_editor_lists( $prop['contact_lists'] );

	wp_die();
}


add_action(
	'wp_ajax_wpcf7-sendinblue-email-template',
	'wpcf7_ajax_sendinblue_email_template',
	10, 0
);

/**
 * Ajax action to update the email templates in the Brevo editor panel.
 */
function wpcf7_ajax_sendinblue_email_template() {
	$contact_form = wpcf7_contact_form( wpcf7_superglobal_get( 'id' ) );

	$prop = wp_parse_args(
		$contact_form ? $contact_form->prop( 'sendinblue' ) : array(),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

	wpcf7_sendinblue_editor_templates( $prop['email_template'] );

	wp_die();
}
