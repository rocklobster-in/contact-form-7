<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action(
	'wp_ajax_wpcf7-sendinblue',
	'wpcf7_ajax_sendinblue',
	10, 0
);

function wpcf7_ajax_sendinblue() {
	$contact_form = wpcf7_contact_form( wpcf7_superglobal_get( 'id' ) );

	if ( $contact_form ) {
		wpcf7_sendinblue_editor_table();
	}

	wp_die();
}
