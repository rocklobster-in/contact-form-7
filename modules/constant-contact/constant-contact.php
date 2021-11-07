<?php

include_once path_join(
	WPCF7_PLUGIN_MODULES_DIR,
	'constant-contact/service.php'
);

include_once path_join(
	WPCF7_PLUGIN_MODULES_DIR,
	'constant-contact/contact-post-request.php'
);


add_action(
	'wpcf7_init',
	'wpcf7_constant_contact_register_service',
	5, 0
);

/**
 * Registers the Constant Contact service.
 */
function wpcf7_constant_contact_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_category( 'email_marketing',
		__( 'Email marketing', 'contact-form-7' )
	);

	$service = WPCF7_ConstantContact::get_instance();
	$integration->add_service( 'constant_contact', $service );
}


add_action(
	'wpcf7_save_contact_form',
	'wpcf7_constant_contact_save_contact_form',
	10, 1
);

/**
 * Callback to the wpcf7_save_contact_form action hook.
 */
function wpcf7_constant_contact_save_contact_form( $contact_form ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	$additional_settings = $contact_form->additional_setting(
		'constant_contact',
		false
	);

	$list_names = array();

	$pattern = '/[\t ]*('
		. "'[^']*'"
		. '|'
		. '"[^"]*"'
		. '|'
		. '[^,]*?'
		. ')[\t ]*(?:[,]+|$)/';

	foreach ( $additional_settings as $setting ) {
		if ( preg_match_all( $pattern, $setting, $matches ) ) {
			foreach ( $matches[1] as $match ) {
				$name = trim( wpcf7_strip_quote( $match ) );

				if ( '' !== $name ) {
					$list_names[] = $name;
				}
			}
		}
	}

	$list_names = array_unique( $list_names );

	$key = sprintf( 'wpcf7_contact_form:%d', $contact_form->id() );

	$service->update_contact_lists( array( $key => $list_names ) );
}


add_action( 'wpcf7_submit', 'wpcf7_constant_contact_submit', 10, 2 );

/**
 * Callback to the wpcf7_submit action hook. Creates a contact
 * based on the submission.
 */
function wpcf7_constant_contact_submit( $contact_form, $result ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	if ( $contact_form->in_demo_mode() ) {
		return;
	}

	$do_submit = true;

	if ( empty( $result['status'] )
	or ! in_array( $result['status'], array( 'mail_sent' ) ) ) {
		$do_submit = false;
	}

	$additional_settings = $contact_form->additional_setting(
		'constant_contact',
		false
	);

	foreach ( $additional_settings as $setting ) {
		if ( in_array( $setting, array( 'off', 'false', '0' ), true ) ) {
			$do_submit = false;
			break;
		}
	}

	$do_submit = apply_filters( 'wpcf7_constant_contact_submit',
		$do_submit, $contact_form, $result
	);

	if ( ! $do_submit ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();

	$consented = true;

	foreach ( $contact_form->scan_form_tags( 'feature=name-attr' ) as $tag ) {
		if ( $tag->has_option( 'consent_for:constant_contact' )
		and null == $submission->get_posted_data( $tag->name ) ) {
			$consented = false;
			break;
		}
	}

	if ( ! $consented ) {
		return;
	}

	$request_builder_class_name = apply_filters(
		'wpcf7_constant_contact_contact_post_request_builder',
		'WPCF7_ConstantContact_ContactPostRequest'
	);

	if ( ! class_exists( $request_builder_class_name ) ) {
		return;
	}

	$request_builder = new $request_builder_class_name;
	$request_builder->build( $submission );

	if ( ! $request_builder->is_valid() ) {
		return;
	}

	if ( $email = $request_builder->get_email_address()
	and $service->email_exists( $email ) ) {
		return;
	}

	$service->create_contact( $request_builder->to_array() );
}
