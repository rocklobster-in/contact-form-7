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


function wpcf7_doi_default_email_callback( $args ) {
	if ( ! isset( $args['token'] ) or ! isset( $args['email_to'] ) ) {
		return;
	}

	$site_title = wp_specialchars_decode(
		get_bloginfo( 'name' ),
		ENT_QUOTES
	);

	$link = add_query_arg(
		array( 'doitoken' => $args['token'] ),
		home_url()
	);

	$to = $args['email_to'];

	$subject = sprintf(
		/* translators: %s: blog name */
		__( 'Opt-in confirmation from %s', 'contact-form-7' ),
		$site_title
	);

	$message = sprintf(
		/* translators: 1: blog name, 2: confirmation link */
		__( 'Hello,

This is a confirmation email sent from %1$s.

We have received your submission to our web form, according to which you have allowed us to add you to our contact list. But, the process has not yet been completed. To complete it, please click the following link.

%2$s

If it was not your intention, or if you have no idea why you received this message, please do not click on the link, and ignore this message. We will never collect or use your personal data without your clear consent.

Sincerely,
%1$s', 'contact-form-7' ),
		$site_title,
		$link
	);

	wp_mail( $to, $subject, $message );
}
