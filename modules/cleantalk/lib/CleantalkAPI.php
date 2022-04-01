<?php
namespace Cleantalk\CF7_Integration;

class CleantalkAPI
{
	const URL = 'https://api.cleantalk.org';

	/**
	* Wrapper for sfw_logs API method
	* @param integer connect timeout
	* @return type
	* returns mixed STRING || array('error' => true, 'error_string' => STRING)
	*/
	static public function method__sfw_logs($api_key, $data, $do_check = true){

		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'sfw_logs',
			'data' => json_encode($data),
			'rows' => count($data),
			'timestamp' => time()
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'sfw_logs') : $result;

		return $result;
	}

	/**
	 * Function gets spam report
	 *
	 * @param string website host
	 * @param integer report days
	 * @return type
	 */
	static public function method__spam_check_cms($api_key, $data, $date = null, $do_check = true)
	{
		$request=Array(
			'method_name' => 'spam_check_cms',
			'auth_key' => $api_key,
			'data' => is_array($data) ? implode(',',$data) : $data,
		);

		if($date) $request['date'] = $date;

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'spam_check_cms') : $result;

		return $result;
	}

	/*
	* Wrapper for 2s_blacklists_db API method
	*
	* returns mixed STRING || array('error' => true, 'error_string' => STRING)
	*/
	static public function method__get_2s_blacklists_db($api_key, $do_check = true){

		$request = array(
			'method_name' => '2s_blacklists_db',
			'auth_key' => $api_key,
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, '2s_blacklists_db') : $result;

		return $result;
	}

	/**
	 * Function gets access key automatically
	 *
	 * @param string website admin email
	 * @param string website host
	 * @param string website platform
	 * @return type
	 */
	static public function method__get_api_key($email, $host, $platform, $timezone = null, $language = null, $ip = null, $white_label = 0, $hoster_api_key = '', $do_check = true)
	{
		$request = array(
			'method_name'          => 'get_api_key',
			'product_name'         => 'antispam',
			'email'                => $email,
			'website'              => $host,
			'platform'             => $platform,
			'timezone'             => $timezone,
			'http_accept_language' => $language,
			'user_ip'              => $ip,
			'hoster_whitelabel'    => $white_label,
			'hoster_api_key'       => $hoster_api_key,
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'get_api_key') : $result;

		return $result;
	}

	/**
	 * Function gets spam report
	 *
	 * @param string website host
	 * @param integer report days
	 * @return type
	 */
	static public function method__get_antispam_report($host, $period = 1)
	{
		$request=Array(
			'method_name' => 'get_antispam_report',
			'hostname' => $host,
			'period' => $period
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'get_antispam_report') : $result;

		return $result;
	}

	/**
	 * Function gets spam statistics
	 *
	 * @param string website host
	 * @param integer report days
	 * @return type
	 */
	static public function method__get_antispam_report_breif($api_key, $do_check = true)
	{
		$request = array(
			'method_name' => 'get_antispam_report_breif',
			'auth_key' => $api_key,
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'get_antispam_report_breif') : $result;

		return $result;
	}

	/**
	 * Function gets information about renew notice
	 *
	 * @param string api_key
	 * @return type
	 */
	static public function method__notice_validate_key($api_key, $path_to_cms, $do_check = true)
	{
		$request = array(
			'method_name' => 'notice_validate_key',
			'auth_key' => $api_key,
			'path_to_cms' => $path_to_cms
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'notice_validate_key') : $result;

		return $result;
	}

	/**
	 * Function gets information about renew notice
	 *
	 * @param string api_key
	 * @return type
	 */
	static public function method__notice_paid_till($api_key, $do_check = true)
	{
		$request = array(
			'method_name' => 'notice_paid_till',
			'auth_key' => $api_key
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'notice_paid_till') : $result;

		return $result;
	}

	static public function method__security_logs($api_key, $data, $do_check = true)
	{
		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_logs',
			'timestamp' => current_time('timestamp'),
			'data' => json_encode($data),
			'rows' => count($data),
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_logs__sendFWData($api_key, $data, $do_check = true){

		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_logs',
			'timestamp' => current_time('timestamp'),
			'data_fw' => json_encode($data),
			'rows_fw' => count($data),
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_logs__feedback($api_key, $do_check = true)
	{
		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_logs',
			'data' => '0',
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_firewall_data($api_key, $do_check = true){

		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_firewall_data',
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_firewall_data_file($api_key, $do_check = true){

		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_firewall_data_file',
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_linksscan_logs($api_key, $scan_time, $scan_result, $links_total, $links_list, $do_check = true)
	{
		$request = array(
			'auth_key' => $api_key,
			'method_name' => 'security_linksscan_logs',
			'started' => $scan_time,
			'result' => $scan_result,
			'total_links_found' => $links_total,
			'links_list' => $links_list,
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_mscan_logs($api_key, $service_id, $scan_time, $scan_result, $scanned_total, $modified, $unknown, $do_check = true)
	{
		$request = array(
			'method_name'        => 'security_mscan_logs',
			'auth_key'           => $api_key,
			'service_id'         => $service_id,
			'started'            => $scan_time,
			'result'             => $scan_result,
			'total_core_files'   => $scanned_total,
		);

		if(!empty($modified)){
			$request['failed_files']      = json_encode($modified);
			$request['failed_files_rows'] = count($modified);
		}
		if(!empty($unknown)){
			$request['unknown_files']      = json_encode($unknown);
			$request['unknown_files_rows'] = count($unknown);
		}

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	static public function method__security_mscan_files($api_key, $file_path, $file, $file_md5, $weak_spots, $do_check = true)
	{
		$request = array(
			'method_name' => 'security_mscan_files',
			'auth_key' => $api_key,
			'path_to_sfile' => $file_path,
			'attached_sfile' => $file,
			'md5sum_sfile' => $file_md5,
			'dangerous_code' => $weak_spots,
		);

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result) : $result;

		return $result;
	}

	/**
	 * Function gets spam domains report
	 *
	 * @param string api key
	 * @param integer report days
	 * @return type
	 */
	static public function method__backlinks_check_cms($api_key, $data, $date = null, $do_check = true)
	{
		$request = array(
			'method_name' => 'backlinks_check_cms',
			'auth_key'    => $api_key,
			'data'        => is_array($data) ? implode(',',$data) : $data,
		);

		if($date) $request['date'] = $date;

		$result = self::send_request($request);
		$result = $do_check ? self::check_response($result, 'backlinks_check_cms') : $result;

		return $result;
	}

	/**
	 * Function sends raw request to API server
	 *
	 * @param string url of API server
	 * @param array data to send
	 * @param boolean is data have to be JSON encoded or not
	 * @param integer connect timeout
	 * @return type
	 */
	static public function send_request($data, $url = self::URL, $timeout = 5, $ssl = false)
	{
		// Possibility to switch API url
		$url = defined('CLEANTALK_API_URL') ? CLEANTALK_API_URL : $url;

		// Adding agent version to data
		if(defined('CLEANTALK_AGENT'))
			$data['agent'] = CLEANTALK_AGENT;

		// Make URL string
		$data_string = http_build_query($data);
		$data_string = str_replace("&amp;", "&", $data_string);

		if (function_exists('curl_init')){

			$ch = curl_init();

			// Set diff options
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

			// Switch on/off SSL
			if ($ssl === true) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }else{
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			}

			// Make a request
			$result = curl_exec($ch);
			$errors = curl_error($ch);
			curl_close($ch);

			// Get cURL error if result failed
			if($result === false){

				// And retry with SSL enabled
				if($ssl === false)
					return self::send_request($data, $url, $timeout, true);

			}

		}else
			$errors = 'CURL_NOT_INSTALLED';

		// Trying to use file_get_contents() to make a API call
		if(!empty($errors) && ini_get('allow_url_fopen')){

			$opts = array(
				'http'=>array(
					'method'  => "POST",
					'timeout' => $timeout,
					'content' => $data_string,
				)
			);
			$context = stream_context_create($opts);
			$result = file_get_contents($url, 0, $context);

		}else
			$errors .= '_AND_ALLOW_URL_FOPEN_IS_DISABLED';

		if(empty($result) && !empty($errors))
			return json_encode(array('error' => true, 'error_string' => $errors));

		return $result;
	}

	/**
	 * Function checks server response
	 *
	 * @param string result
	 * @param string request_method
	 * @return mixed (array || array('error' => true))
	 */
	static public function check_response($result, $method_name = null)
	{

		$out = array();

		// Errors handling

		// Bad connection
		if(empty($result)){
			$out = array(
				'error' => true,
				'error_string' => 'CONNECTION_ERROR'
			);
		}

		// JSON decode errors
		$result = json_decode($result, true);
		if(empty($result)){
			$out = array(
				'error' => true,
				'error_string' => 'JSON_DECODE_ERROR'
			);
		}

		// cURL error
		if(!empty($result['error'])){
			$out = array(
				'error' => true,
				'error_string' => 'CONNECTION_ERROR: ' . $result['error_string'],
			);
		}

		// Server errors
		if($result && (isset($result['error_no']) || isset($result['error_message']))){
			$out = array(
				'error' => true,
				'error_string' => "SERVER_ERROR NO: {$result['error_no']} MSG: {$result['error_message']}",
				'error_no' => $result['error_no'],
				'error_message' => $result['error_message']
			);
		}

		// Pathces for different methods
		if(empty($out['error'])){

			// mehod_name = notice_validate_key
			if($method_name == 'notice_validate_key' && isset($result['valid']))
				$out = $result;

			// Other methods
			if(isset($result['data']) && is_array($result['data'])){
				$out = $result['data'];
			}
		}

		// mehod_name = get_antispam_report_breif
		if($method_name == 'get_antispam_report_breif'){

			if(empty($out['error']))
				$result = $result['data'];

			for( $tmp = array(), $i = 0; $i < 7; $i++ )
				$tmp[ date( 'Y-m-d', time() - 86400 * 7 + 86400 * $i ) ] = 0;

			$result['spam_stat']    = array_merge( $tmp, isset($result['spam_stat']) ? $result['spam_stat'] : array() );
			$result['top5_spam_ip'] = isset($result['top5_spam_ip']) ? $result['top5_spam_ip'] : array();
			$out = array_merge($result, $out);
		}

		return $out;

	}
}
