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

	if ( WPCF7_CLEANTALK::get_instance()->is_active() ) {
		wpcf7_apbct_cookie();
	}
}

add_action(
	'wp_enqueue_scripts',
	'wpcf7_cleantalk_enqueue_scripts',
	20, 0
);
function wpcf7_cleantalk_enqueue_scripts() {
	$service = WPCF7_CLEANTALK::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	wp_register_script(
		'wpcf7-cleantalk-public',
		wpcf7_plugin_url( 'modules/cleantalk/src/index.js' ),
		array(),
		WPCF7_VERSION,
		true
	);

	wp_enqueue_script( 'wpcf7-cleantalk-public' );
}

add_filter('wpcf7_form_hidden_fields', 'wpcf7_cleantalk_add_hidden_fields', 100, 1);
function wpcf7_cleantalk_add_hidden_fields($fields) {
	$service = WPCF7_CLEANTALK::get_instance();

	if ( ! $service->is_active() ) {
		return $fields;
	}

	return array_merge( $fields, array(
		WPCF7_CLEANTALK::$checkjs_field_id => '0',
		WPCF7_CLEANTALK::$timestamp_field_id => '0',
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
	window.addEventListener("DOMContentLoaded", function() {
		setTimeout(function(){
            const ct_checkjs_input_name = "' . WPCF7_CLEANTALK::$checkjs_field_id . '";
            const ct_checkjs_input_element = document.getElementsByName(ct_checkjs_input_name)[0];
            if ( ct_checkjs_input_element !== null ) {
                const ct_checkjs_input_value = ct_checkjs_input_element.value;
                ct_checkjs_input_element.value = ct_checkjs_input_element.value.replace(ct_checkjs_input_value, "' . $service->get_checkjs_value() . '");
            }
		});
		const ct_timestamp_input_name = "' . WPCF7_CLEANTALK::$timestamp_field_id . '";
        const ct_timestamp_input_element = document.getElementsByName(ct_timestamp_input_name)[0];
        if ( ct_timestamp_input_element !== null ) {
            ct_timestamp_input_element.value = new Date().getTime()/1000;
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
	$checkjs = $service->js_test(WPCF7_CLEANTALK::$checkjs_field_id, $_POST);
	$apbct_timestamp = isset($_POST[WPCF7_CLEANTALK::$timestamp_field_id]) ? (int) $_POST[WPCF7_CLEANTALK::$timestamp_field_id] : 0;

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
	$ct_request->agent = 'wordpress-cf7-' . WPCF7_CLEANTALK::$version;
	$ct_request->sender_email = $sender_email;
	$ct_request->sender_ip = $sender_ip;
	$ct_request->sender_nickname = $sender_nickname;
	$ct_request->js_on = $checkjs;
	$ct_request->message = $message;
	$ct_request->submit_time = $apbct_timestamp !== 0 ? time() - $apbct_timestamp : null;
	$ct_request->post_info = json_encode(array('comment_type' => 'contact_form_wordpress_cf7__included_integration'));
	$ct_request->sender_info = json_encode(array(
		'has_scrolled' => isset($_COOKIE['ct_has_scrolled']) ? json_encode(htmlspecialchars($_COOKIE['ct_has_scrolled'])) : null,
		'mouse_moved' => isset($_COOKIE['ct_mouse_moved']) ? json_encode(htmlspecialchars($_COOKIE['ct_mouse_moved'])) : null,
		'cookies_enabled' => wpcf7_apbct_cookies_test(),
	));

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

/**
 * Set Cookies test for cookie test
 * Sets cookies with params timestamp && landing_timestamp && previous_referer
 * Sets test cookie with all other cookies
 * @return bool
 */
function wpcf7_apbct_cookie()
{
	// Prevent headers sent error
	if ( headers_sent() ) {
		return false;
	}

	// Cookie names to validate
	$cookie_test_value = array(
		'cookies_names' => array(),
		'check_value'   => WPCF7_CLEANTALK::get_instance()->get_apikey(),
	);

	// We need to skip the domain attribute for prevent including the dot to the cookie's domain on the client.
	$domain = '';

	// Submit time
	$apbct_timestamp = time();
	$cookie_test_value['cookies_names'][] = 'apbct_timestamp';
	$cookie_test_value['check_value']    .= $apbct_timestamp;

	// Previous referer
	$apbct_referrer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) :'';
	$cookie_test_value['cookies_names'][] = 'apbct_prev_referer';
	$cookie_test_value['check_value']    .= $apbct_referrer;

	// Cookies test
	$cookie_test_value['check_value'] = md5($cookie_test_value['check_value']);

	wpcf7_apbct_cookie_set('apbct_timestamp', (string)$apbct_timestamp, 0, '/', $domain, null, true);
	wpcf7_apbct_cookie_set('apbct_prev_referer', $apbct_referrer, 0, '/', $domain, null, true);
	wpcf7_apbct_cookie_set('apbct_cookies_test', urlencode(json_encode($cookie_test_value)), 0, '/', $domain, null, true);
}

/**
 * Cookies test for sender
 * Also checks for valid timestamp in $_COOKIE['apbct_timestamp'] and other apbct_ COOKIES
 * @return null|int null|0|1
 * @throws JsonException
 */
function wpcf7_apbct_cookies_test() {
	if ( isset($_COOKIE['apbct_cookies_test']) ) {
		$cookie_test = json_decode(urldecode($_COOKIE['apbct_cookies_test']), true);

		if ( ! is_array($cookie_test) ) {
			return 0;
		}

		$check_string = WPCF7_CLEANTALK::get_instance()->get_apikey();
		foreach ( $cookie_test['cookies_names'] as $cookie_name ) {
			$check_string .= isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : '';
		}

		if ( $cookie_test['check_value'] == md5($check_string) ) {
			return 1;
		}

		return 0;
	}

	return null;
}

function wpcf7_apbct_cookie_set(
	$name,
	$value = '',
	$expires = 0,
	$path = '',
	$domain = '',
	$secure = null,
	$httponly = false,
	$samesite = 'Lax'
) {
	// For PHP 7.3+ and above
    if (version_compare(phpversion(), '7.3.0', '>=')) {

        $secure = ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) || $_SERVER['SERVER_PORT'] == 443;

        $params = array(
	        'expires' => $expires,
	        'path' => $path,
	        'domain' => $domain,
	        'secure' => $secure,
	        'httponly' => $httponly,
        );

        if ($samesite) {
	        $params['samesite'] = $samesite;
        }

        /**
         * @psalm-suppress InvalidArgument
         */
        setcookie($name, $value, $params);
        // For PHP 5.6 - 7.2
    } else {
        setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }
}
