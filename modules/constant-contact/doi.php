<?php

add_action(
	'doihelper_init',
	'wpcf7_constant_contact_doi_register_agent',
	10, 0
);

function wpcf7_constant_contact_doi_register_agent() {
	if ( ! function_exists( 'doihelper_register_agent' ) ) {
		return;
	}

	doihelper_register_agent( 'wpcf7_constant_contact', array(
		'optin_callback' => function ( $properties ) {
			$service = WPCF7_ConstantContact::get_instance();

			if ( $service->is_active() ) {
				$service->create_contact( $properties );
			}
		},
		'email_callback' => function ( $args ) {
			// todo: send email
		},
	) );
}


function wpcf7_constant_contact_doi_create_session( $args = '' ) {
	if ( ! function_exists( 'doihelper_start_session' ) ) {
		return false;
	}

	return doihelper_start_session( 'wpcf7_constant_contact', $args );
}
