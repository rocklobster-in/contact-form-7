<?php

function wpcf7_do_doi( $agent_name, $contact_form, $args = '' ) {
	$do_doi = ! array_filter(
		$contact_form->additional_setting( 'doi', false ),
		function ( $setting ) {
			return in_array( $setting, array( 'off', 'false', '0' ), true );
		}
	);

	return apply_filters( 'wpcf7_do_doi', $do_doi,
		$agent_name, $contact_form, $args
	);
}


function wpcf7_doi_create_session( $agent_name, $args = '' ) {
	if ( ! function_exists( 'doihelper_start_session' ) ) {
		return false;
	}

	if ( 'wpcf7_' !== substr( $agent_name, 0, 6 ) ) {
		$agent_name = sprintf( 'wpcf7_%s', $agent_name );
	}

	return (bool) doihelper_start_session( $agent_name, $args );
}
