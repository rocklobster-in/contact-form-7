<?php

/**
 * CleanTalk Anti-Spam module main file
 *
 * @author CleanTalk team https://cleantalk.org
 * @version 1.0.0
 */

wpcf7_include_module_file( 'cleantalk/service.php' );

add_action( 'wpcf7_init', 'wpcf7_cleantalk_register_service', 15, 0 );

/**
 * Registers the CleanTalk Anti-Spam service.
 */
function wpcf7_cleantalk_register_service() {
	$integration = WPCF7_Integration::get_instance();
	$integration->add_service('cleantalk', WPCF7_CLEANTALK::get_instance());
}

add_filter('wpcf7_form_hidden_fields', 'wpcf7_cleantalk_add_hidden_fields', 100, 1);
function wpcf7_cleantalk_add_hidden_fields($fields) {
	$service = WPCF7_CLEANTALK::get_instance();

	if ( ! $service->is_active() ) {
		return $fields;
	}

	return array_merge( $fields, array(
		WPCF7_CLEANTALK::$hidden_field_id => '0',
	) );
}

add_filter('wpcf7_form_elements', 'wpcf7_cleantalk_add_hidden_fields_filler', 100, 1);
function wpcf7_cleantalk_add_hidden_fields_filler($html) {
	$service = WPCF7_CLEANTALK::get_instance();

	if ( ! $service->is_active() ) {
		return $html;
	}

	$script = '
	<script type="text/javascript">
		setTimeout(function(){
            const ct_hidden_input_name = "' . WPCF7_CLEANTALK::$hidden_field_id . '";
            const ct_hidden_input_element = document.getElementsByName(ct_hidden_input_name)[0];
            if ( ct_hidden_input_element !== null ) {
                const ct_input_value = ct_hidden_input_element.value;
                ct_hidden_input_element.value = ct_hidden_input_element.value.replace(ct_input_value, "' . $service->get_checkjs_value() . '");
            }
		});
	</script>';

	$html .= $script;

	return $html;
}
