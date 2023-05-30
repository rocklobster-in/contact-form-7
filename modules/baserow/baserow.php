<?php
/**
 * Baserow module main file
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
 * Callback to the wpcf7_submit action hook.
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

    $prop = wp_parse_args(
        $contact_form->prop('baserow'),
        array(
            'database_id' => 0,
            'mapping' => array()
        )
    );

    $attributes = wpcf7_baserow_collect_parameters();

    $data = array();
    foreach ($attributes as $attribute_key => $attribute) {
        $data[$prop['mapping'][$attribute_key]] = $attribute;
    }

    $result = $service->create_row($contact_form->baserow['database_id'], $data );
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
