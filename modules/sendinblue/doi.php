<?php
/**
 * Double Opt-In Helper-related functions
 *
 * @link https://contactform7.com/doi-helper/
 */


add_action(
	'doihelper_init',
	'wpcf7_sendinblue_doi_register_agent',
	10, 0
);

/**
 * Registers wpcf7_sendinblue as an agent.
 */
function wpcf7_sendinblue_doi_register_agent() {
	wpcf7_doi_register_agent( 'sendinblue', array(
		'optin_callback' => apply_filters(
			'wpcf7_sendinblue_doi_optin_callback',
			'wpcf7_sendinblue_doi_default_optin_callback'
		),
		'email_callback' => apply_filters(
			'wpcf7_sendinblue_doi_email_callback',
			'wpcf7_sendinblue_doi_default_email_callback'
		),
	) );
}


/**
 * Default optin_callback function.
 */
function wpcf7_sendinblue_doi_default_optin_callback( $properties ) {
	$service = WPCF7_Sendinblue::get_instance();

	if ( $service->is_active() ) {
		$service->create_contact( $properties );
	}
}


/**
 * Default email_callback function.
 */
function wpcf7_sendinblue_doi_default_email_callback( $args ) {
	wpcf7_doi_default_email_callback( $args );
}
