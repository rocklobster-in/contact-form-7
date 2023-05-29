<?php
/**
 * Baserow module main file
 *
 * @link https://contactform7.com/baserow-integration/
 */

wpcf7_include_module_file('baserow/service.php');
wpcf7_include_module_file('baserow/contact-form-properties.php');
wpcf7_include_module_file('baserow/doi.php');


add_action('wpcf7_init', 'wpcf7_baserow_register_service', 10, 0);

/**
 * Registers the Baserow service.
 */
function wpcf7_baserow_register_service()
{
    $integration = WPCF7_Integration::get_instance();

    $integration->add_service('baserow',
        WPCF7_Baserow::get_instance()
    );
}


add_action('wpcf7_submit', 'wpcf7_baserow_submit', 10, 2);

/**
 * Callback to the wpcf7_submit action hook. Creates a contact
 * based on the submission.
 */
function wpcf7_baserow_submit($contact_form, $result)
{

    if ($contact_form->in_demo_mode()) {
        return;
    }

    $service = WPCF7_Baserow::get_instance();

    if (!$service->is_active()) {
        return;
    }

    if (empty($result['posted_data_hash'])) {
        return;
    }

    if (empty($result['status'])
        or !in_array($result['status'], array('mail_sent', 'mail_failed'))) {
        return;
    }

    $submission = WPCF7_Submission::get_instance();

    // var_dump($contact_form->scan_form_tags()); die();
    // var_dump($contact_form->baserow['database_id']); die();
    // var_dump($contact_form->baserow['mapping']); die();


    // $service->create_row($contact_form->baserow['database_id'] );


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
        $contact_form->prop('baserow'),
        array(
            'database_id' => 0,
            'mapping' => array()
        )
    );

    // var_dump($prop);
    // die();

    $attributes = wpcf7_baserow_collect_parameters();
    // var_dump($attributes); die();

    $data = array();
    foreach ($attributes as $attribute_key => $attribute) {
        $data[$prop['mapping'][$attribute_key]] = $attribute;
    }

    // var_dump($data); die();


    $result = $service->create_row($contact_form->baserow['database_id'], $data );

    var_dump($result); die();

    /*







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
function wpcf7_baserow_collect_parameters()
{

    $params = array();

    $submission = WPCF7_Submission::get_instance();

    foreach ((array)$submission->get_posted_data() as $name => $val) {
        if ($val) {
            $params += array(
                $name => $val,
            );
        }
    }


    $params = apply_filters(
        'wpcf7_baserow_collect_parameters',
        $params
    );

    return $params;
}
