<?php

include_once path_join(
	WPCF7_PLUGIN_MODULES_DIR,
	'sendinblue/service.php'
);

include_once path_join(
	WPCF7_PLUGIN_MODULES_DIR,
	'sendinblue/contact-form-properties.php'
);


add_action( 'wpcf7_init', 'wpcf7_sendinblue_register_service', 1, 0 );

function wpcf7_sendinblue_register_service() {
	$integration = WPCF7_Integration::get_instance();

	$integration->add_service( 'sendinblue',
		WPCF7_Sendinblue::get_instance()
	);
}


add_action( 'wpcf7_submit', 'wpcf7_sendinblue_submit', 10, 2 );

function wpcf7_sendinblue_submit( $contact_form, $result ) {
	if ( $contact_form->in_demo_mode() ) {
		return;
	}

	$service = WPCF7_Sendinblue::get_instance();

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

	foreach ( $contact_form->scan_form_tags( 'feature=name-attr' ) as $tag ) {
		if ( $tag->has_option( 'consent_for:sendinblue' )
		and null == $submission->get_posted_data( $tag->name ) ) {
			$consented = false;
			break;
		}
	}

	if ( ! $consented ) {
		return;
	}

	$prop = wp_parse_args(
		$contact_form->prop( 'sendinblue' ),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

	if ( ! $prop['enable_contact_list'] ) {
		return;
	}

	$attributes = wpcf7_sendinblue_collect_parameters();

	if ( empty( $attributes['EMAIL'] ) and empty( $attributes['SMS'] ) ) {
		return;
	}

	$contact_id = $service->create_contact( array(
		'email' => $attributes['EMAIL'],
		'attributes' => (object) $attributes,
		'listIds' => (array) $prop['contact_lists'],
	) );

	if ( ! $contact_id ) {
		return;
	}

	if ( ! $prop['enable_transactional_email'] or ! $prop['email_template'] ) {
		return;
	}

	$service->send_email( array(
		'templateId' => absint( $prop['email_template'] ),
		'to' => array(
			array(
				'name' => sprintf(
					'%1$s %2$s',
					$attributes['FIRSTNAME'],
					$attributes['LASTNAME']
				),
				'email' => $attributes['EMAIL'],
			),
		),
		'params' => (object) $attributes,
		'tags' => array( 'Contact Form 7' ),
	) );
}


function wpcf7_sendinblue_collect_parameters() {
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
		'wpcf7_sendinblue_collect_parameters',
		$params
	);

	return $params;
}
