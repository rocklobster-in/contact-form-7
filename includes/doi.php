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
