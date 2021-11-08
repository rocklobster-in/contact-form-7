<?php

add_filter(
	'wpcf7_contact_form_properties',
	'wpcf7_constant_contact_register_property',
	10, 2
);

function wpcf7_constant_contact_register_property( $properties, $contact_form ) {
	$service = WPCF7_ConstantContact::get_instance();

	if ( ! $service->is_active() ) {
		return $properties;
	}

	$properties += array(
		'constant_contact' => array(),
	);

	return $properties;
}
