<?php
/**
 * Double Opt-In Helper-related functions
 *
 * @link https://contactform7.com/doi-helper/
 */


add_action(
	'doihelper_init',
	'wpcf7_constant_contact_doi_register_agent',
	10, 0
);

/**
 * Registers wpcf7_constant_contact as an agent.
 */
function wpcf7_constant_contact_doi_register_agent() {
	wpcf7_doi_register_agent( 'constant_contact', array(
		'optin_callback' => apply_filters(
			'wpcf7_constant_contact_doi_optin_callback',
			'wpcf7_constant_contact_doi_default_optin_callback'
		),
		'email_callback' => apply_filters(
			'wpcf7_constant_contact_doi_email_callback',
			'wpcf7_constant_contact_doi_default_email_callback'
		),
	) );
}


/**
 * Default optin_callback function.
 */
function wpcf7_constant_contact_doi_default_optin_callback( $properties ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( $service->is_active() ) {
		$service->create_contact( $properties );
	}
}


/**
 * Default email_callback function.
 */
function wpcf7_constant_contact_doi_default_email_callback( $args ) {
	wpcf7_doi_default_email_callback( $args );
}
