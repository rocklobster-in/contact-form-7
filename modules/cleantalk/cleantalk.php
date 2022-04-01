<?php

/**
 * CleanTalk Anti-Spam module main file
 *
 * @author CleanTalk team https://cleantalk.org
 * @version 1.0.0
 */

wpcf7_include_module_file( 'cleantalk/service.php' );
wpcf7_include_module_file( 'cleantalk/lib/GetFieldsAny.php' );
wpcf7_include_module_file( 'cleantalk/lib/Cleantalk.php' );
wpcf7_include_module_file( 'cleantalk/lib/CleantalkHelper.php' );
wpcf7_include_module_file( 'cleantalk/lib/CleantalkRequest.php' );
wpcf7_include_module_file( 'cleantalk/lib/cleantalk-php-patch.php' );
wpcf7_include_module_file( 'cleantalk/lib/CleantalkAPI.php' );
wpcf7_include_module_file( 'cleantalk/lib/CleantalkResponse.php' );
wpcf7_include_module_file( 'cleantalk/lib/TransportException.php' );

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

add_filter( 'wpcf7_spam', 'wpcf7_cleantalk_test_spam', 8, 2 );
function wpcf7_cleantalk_test_spam($spam, $submission) {
	$service = WPCF7_CLEANTALK::get_instance();

	if ( ! $service->is_active() ) {
		return $spam;
	}
	$checkjs = $service->js_test(WPCF7_CLEANTALK::$hidden_field_id, $_POST);

	$gfa = new \Cleantalk\CF7_Integration\GetFieldsAny($_POST);
	$ct_temp_msg_data = $gfa->getFields();

	$sender_email    = $ct_temp_msg_data['email'] ?: '';
	$sender_nickname = $ct_temp_msg_data['nickname'] ?: '';
	$subject         = $ct_temp_msg_data['subject'] ?: '';
	$message         = $ct_temp_msg_data['message'] ?: array();
	if ( $subject !== '' ) {
		$message = array_merge(array('subject' => $subject), $message);
	}

	$sender_ip = null;
	if (isset($_SERVER['REMOTE_ADDR'])) {
		$sender_ip = $_SERVER['REMOTE_ADDR'];
	}

	// The facility in which to store the query parameters
	$ct_request = new \Cleantalk\CF7_Integration\CleantalkRequest();

	$ct_request->auth_key = $service->get_apikey();
	$ct_request->agent = 'contact_form_wordpress_cf7__included_integration__ver_' . WPCF7_CLEANTALK::$version;
	$ct_request->sender_email = $sender_email;
	$ct_request->sender_ip = $sender_ip;
	$ct_request->sender_nickname = $sender_nickname;
	$ct_request->js_on = $checkjs;
	$ct_request->message = $message;
	$ct_request->post_info = json_encode(array('comment_type' => 'contact_form_wordpress_cf7__included_integration'));

	$ct = new \Cleantalk\CF7_Integration\Cleantalk();
	$ct->server_url = 'https://moderate.cleantalk.org/api2.0/';

	// Check
	$ct_result = $ct->isAllowMessage($ct_request);

	if ( $ct_result->allow == 0 ) {
		$ct_cf7_comment = $ct_result->comment;

		add_filter('wpcf7_display_message', function($message, $status = 'spam') use ( $ct_cf7_comment ) {
			if ( $status === 'spam' ) {
				$message = $ct_cf7_comment;
			}
			return $message;
		}, 10, 2);

		$spam = defined('WPCF7_VERSION') && WPCF7_VERSION >= '3.0.0';
	}

	return $spam;
}
