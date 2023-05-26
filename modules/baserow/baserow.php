<?php
/**
 * Baserow module main file
 *
 * @link https://contactform7.com/baserow-integration/
 */

wpcf7_include_module_file( 'baserow/service.php' );
wpcf7_include_module_file( 'baserow/contact-form-properties.php' );
wpcf7_include_module_file( 'baserow/doi.php' );


add_action( 'wpcf7_init', 'wpcf7_baserow_register_service', 10, 0 );

/**
 * Registers the Baserow service.
 */
function wpcf7_baserow_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_service( 'baserow',
		WPCF7_Baserow::get_instance()
	);
}


add_action( 'wpcf7_submit', 'wpcf7_baserow_submit', 10, 2 );

/**
 * Callback to the wpcf7_submit action hook. Creates a contact
 * based on the submission.
 */
function wpcf7_baserow_submit( $contact_form, $result ) {

	if ( $contact_form->in_demo_mode() ) {
		return;
	}

	$service = WPCF7_Baserow::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	if ( empty( $result['posted_data_hash'] ) ) {
		return;
	}

	if ( empty( $result['status'] )
	or ! in_array( $result['status'], array( 'mail_sent', 'mail_failed' ) ) ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();

	$consented = true;

    // $contact_form->additional_settings
    // $contact_form->baserow

    // var_dump($contact_form); die();
    // var_dump($submission); die();

    /*
	foreach ( $contact_form->scan_form_tags( 'feature=name-attr' ) as $tag ) {
		if ( $tag->has_option( 'consent_for:baserow' )
		and null == $submission->get_posted_data( $tag->name ) ) {
			$consented = false;
			break;
		}
	}


	if ( ! $consented ) {
		return;
	}
    */

	$prop = wp_parse_args(
		$contact_form->prop( 'baserow' ),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

    /*
    var_dump($prop); die();

	if ( ! $prop['enable_contact_list'] ) {
		return;
	}
    */

	$attributes = wpcf7_baserow_collect_parameters();


	$params = array(
		'contact' => array(),
		'email' => array(),
	);

    /*

	if ( ! empty( $attributes['EMAIL'] ) or ! empty( $attributes['SMS'] ) ) {
		$params['contact'] = apply_filters(
			'wpcf7_baserow_contact_parameters',
			array(
				'email' => $attributes['EMAIL'],
				'attributes' => (object) $attributes,
				'listIds' => (array) $prop['contact_lists'],
				'updateEnabled' => false,
			)
		);
	}

	if ( $prop['enable_transactional_email'] and $prop['email_template'] ) {
		$first_name = isset( $attributes['FIRSTNAME'] )
			? trim( $attributes['FIRSTNAME'] )
			: '';

		$last_name = isset( $attributes['LASTNAME'] )
			? trim( $attributes['LASTNAME'] )
			: '';

		if ( $first_name or $last_name ) {
			$email_to_name = sprintf(

				_x( '%1$s %2$s', 'personal name', 'contact-form-7' ),
				$first_name,
				$last_name
			);
		} else {
			$email_to_name = '';
		}

		$params['email'] = apply_filters(
			'wpcf7_baserow_email_parameters',
			array(
				'templateId' => absint( $prop['email_template'] ),
				'to' => array(
					array(
						'name' => $email_to_name,
						'email' => $attributes['EMAIL'],
					),
				),
				'params' => (object) $attributes,
				'tags' => array( 'Contact Form 7' ),
			)
		);
	}

	if ( is_email( $attributes['EMAIL'] ) ) {
		$token = null;

		do_action_ref_array( 'wpcf7_doi', array(
			'wpcf7_baserow',
			array(
				'email_to' => $attributes['EMAIL'],
				'properties' => $params,
			),
			&$token,
		) );

		if ( isset( $token ) ) {
			return;
		}
	}

	if ( ! empty( $params['contact'] ) ) {
		$contact_id = $service->create_contact( $params['contact'] );

		if ( $contact_id and ! empty( $params['email'] ) ) {
			$service->send_email( $params['email'] );
		}
	}

    */
}


/**
 * Collects parameters for Baserow contact data based on submission.
 *
 * @return array Baserow contact parameters.
 */
function wpcf7_baserow_collect_parameters() {

	$params = array();

	$submission = WPCF7_Submission::get_instance();

	foreach ( (array) $submission->get_posted_data() as $name => $val ) {
		$name = strtoupper( $name );

		if ( 'YOUR-' == substr( $name, 0, 5 ) ) {
			$name = substr( $name, 5 );
		}

		if ( $val ) {
			$params += array(
				$name => $val,
			);
		}
	}

	if ( isset( $params['SMS'] ) ) {
		$sms = implode( ' ', (array) $params['SMS'] );
		$sms = trim( $sms );

		$plus = '+' == substr( $sms, 0, 1 ) ? '+' : '';
		$sms = preg_replace( '/[^0-9]/', '', $sms );

		if ( 6 < strlen( $sms ) and strlen( $sms ) < 18 ) {
			$params['SMS'] = $plus . $sms;
		} else { // Invalid phone number
			unset( $params['SMS'] );
		}
	}

	if ( isset( $params['NAME'] ) ) {
		$your_name = implode( ' ', (array) $params['NAME'] );
		$your_name = explode( ' ', $your_name );

		if ( ! isset( $params['LASTNAME'] ) ) {
			$params['LASTNAME'] = implode(
				' ',
				array_slice( $your_name, 1 )
			);
		}

		if ( ! isset( $params['FIRSTNAME'] ) ) {
			$params['FIRSTNAME'] = implode(
				' ',
				array_slice( $your_name, 0, 1 )
			);
		}
	}

	$params = apply_filters(
		'wpcf7_baserow_collect_parameters',
		$params
	);


	return $params;
}
