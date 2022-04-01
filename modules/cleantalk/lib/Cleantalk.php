<?php
namespace Cleantalk\CF7_Integration;

/**
 * Cleantalk class create request
 */
class Cleantalk {

	/**
	 * Checked IP
	 * @var string
	 */
	public $sender_ip = null;

	/**
	 * Checked Email
	 * @var string
	 */
	public $sender_email = null;

    /**
	* Maximum data size in bytes
	* @var int
	*/
	private $dataMaxSise = 32768;

	/**
	* Data compression rate
	* @var int
	*/
	private $compressRate = 6;

    /**
	* Server connection timeout in seconds
	* @var int
	*/
	private $server_timeout = 15;

    /**
     * Cleantalk server url
     * @var string
     */
    public $server_url = null;

    /**
     * Last work url
     * @var string
     */
    public $work_url = null;

    /**
     * WOrk url ttl
     * @var int
     */
    public $server_ttl = null;

    /**
     * Time wotk_url changer
     * @var int
     */
    public $server_changed = null;

    /**
     * Flag is change server url
     * @var bool
     */
    public $server_change = false;

    /**
     * Codepage of the data
     * @var bool
     */
    public $data_codepage = null;

    /**
     * API version to use
     * @var string
     */
    public $api_version = '/api2.0';

    /**
     * Use https connection to servers
     * @var bool
     */
    public $ssl_on = false;

    /**
     * Path to SSL certificate
     * @var string
     */
    public $ssl_path = '';

    /**
     * Minimal server response in miliseconds to catch the server
     *
     */
    public $min_server_timeout = 50;

    /**
     * Maximal server response in miliseconds to catch the server
     *
     */
    public $max_server_timeout = 1500;

	/**
	 * Function checks whether it is possible to publish the message
	 *
	 * @param CleantalkRequest $request
	 *
	 * @return CleantalkResponse
	 */
	public function isAllowMessage( CleantalkRequest $request ){
		$request          = $this->filterRequest( $request );
		$filtered_request = $this->createMsg( 'check_message', $request );

		$this->sender_ip    = $filtered_request->sender_ip;
		$this->sender_email = $filtered_request->sender_email;

		return $this->httpRequest( $filtered_request );
	}

	/**
	 * Function checks whether it is possible to publish the message
	 *
	 * @param CleantalkRequest $request
	 *
	 * @return CleantalkResponse
	 */
	public function isAllowUser( CleantalkRequest $request ){
		$request          = $this->filterRequest( $request );
		$filtered_request = $this->createMsg( 'check_newuser', $request );

		$this->sender_ip    = $filtered_request->sender_ip;
		$this->sender_email = $filtered_request->sender_email;

		return $this->httpRequest( $filtered_request );
	}

	/**
	 * Function sends the results of manual moderation
	 *
	 * @param CleantalkRequest $request
	 *
	 * @return CleantalkResponse
	 */
	public function sendFeedback( CleantalkRequest $request ){

		$request          = $this->filterRequest( $request );
		$filtered_request = $this->createMsg( 'send_feedback', $request );

		$this->sender_ip    = $filtered_request->sender_ip;
		$this->sender_email = $filtered_request->sender_email;

		return $this->httpRequest( $filtered_request );
	}

	/**
	 *  Filter request params
	 *
	 * @param CleantalkRequest $request
	 *
	 * @return CleantalkRequest
	 */
    private function filterRequest(CleantalkRequest $request) {

        // general and optional
        foreach ($request as $param => $value) {

			if($param == 'js_on')           { if(!is_int($value))    { $request->$param = null; }}
			if($param == 'submit_time')     { if(!is_int($value))    { $request->$param = null; }}
			if($param == 'message')         { if(!is_string($value)) { $request->$param = null; }} // Should be array, but servers understand only JSON
			if($param == 'example')         { if(!is_string($value)) { $request->$param = null; }} // Should be array, but servers understand only JSON
			if($param == 'sender_info')     { if(!is_string($value)) { $request->$param = null; }} // Should be array, but servers understand only JSON
			if($param == 'post_info')       { if(!is_string($value)) { $request->$param = null; }} // Should be array, but servers understand only JSON
			if($param == 'agent')           { if(!is_string($value)) { $request->$param = null; }}
			if($param == 'sender_nickname') { if(!is_string($value)) { $request->$param = null; }}
			if($param == 'phone')           { if(!is_string($value)) { $request->$param = null; }}
			if($param == 'sender_email')    { if(!is_string($value)) { $request->$param = null; }}
			if($param == 'sender_ip')       { if(!is_string($value)) { $request->$param = null; }}

        }

		return $request;
    }

	/**
     * Compress data and encode to base64
     * @param string
     * @return string
     */
	private function compressData($data = null){

		if (strlen($data) > $this->dataMaxSise && function_exists('gzencode') && function_exists('base64_encode')){

			$localData = gzencode($data, $this->compressRate, FORCE_GZIP);

			if ($localData === false)
				return $data;

			$localData = base64_encode($localData);

			if ($localData === false)
				return $data;

			return $localData;
		}

		return $data;
	}

	/**
	 * Create msg for cleantalk server
	 *
	 * @param string $method
	 * @param CleantalkRequest $request
	 *
	 * @return CleantalkRequest
	 */
    private function createMsg($method, CleantalkRequest $request) {

        switch ($method) {
            case 'check_message':
                // Convert strings to UTF8
                $request->message         = CleantalkHelper::stringToUTF8( $request->message,         $this->data_codepage);
                $request->example         = CleantalkHelper::stringToUTF8( $request->example,         $this->data_codepage);
                $request->sender_email    = CleantalkHelper::stringToUTF8($request->sender_email,    $this->data_codepage);
                $request->sender_nickname = CleantalkHelper::stringToUTF8($request->sender_nickname, $this->data_codepage);

                // $request->message = $this->compressData($request->message);
				// $request->example = $this->compressData($request->example);
                break;

            case 'check_newuser':
                // Convert strings to UTF8
                $request->sender_email    = CleantalkHelper::stringToUTF8($request->sender_email,    $this->data_codepage);
                $request->sender_nickname = CleantalkHelper::stringToUTF8($request->sender_nickname, $this->data_codepage);
                break;

            case 'send_feedback':
                if (is_array($request->feedback)) {
                    $request->feedback = implode(';', $request->feedback);
                }
                break;
        }

        $request->method_name = $method;

        // Removing non UTF8 characters from request, because non UTF8 or malformed characters break json_encode().
        foreach ($request as $param => $value) {
            if(is_array($request->$param))
				$request->$param = CleantalkHelper::removeNonUTF8FromArray($value);
			if(is_string($request->$param) || is_int($request->$param))
				$request->$param = CleantalkHelper::removeNonUTF8FromString($value);
        }

        return $request;
    }

	/**
	 * Send JSON request to servers
	 *
	 * @param $data
	 * @param $url
	 * @param int $server_timeout
	 *
	 * @return boolean|\CleantalkResponse
	 */
    private function sendRequest($data, $url, $server_timeout = 3)
	{
        // Convert to array
        $data = (array)json_decode(json_encode($data), true);

		//Cleaning from 'null' values
		$tmp_data = array();
		foreach($data as $key => $value){
			if($value !== null)
				$tmp_data[$key] = $value;
		}
		$data = $tmp_data;
		unset($key, $value, $tmp_data);

        // Convert to JSON
        $data = json_encode($data);

        if (isset($this->api_version)) {
            $url = $url . $this->api_version;
        }

        // Switching to secure connection
        if ($this->ssl_on && !preg_match("/^https:/", $url)){
            $url = preg_replace("/^(http)/i", "$1s", $url);
        }

        $result = false;
        $curl_error = null;
		if(function_exists('curl_init')) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $server_timeout);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // receive server response ...
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); // resolve 'Expect: 100-continue' issue
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0); // see http://stackoverflow.com/a/23322368

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disabling CA cert verivication and
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);     // Disabling common name verification

            if ($this->ssl_on && $this->ssl_path != '') {
				curl_setopt($ch, CURLOPT_CAINFO, $this->ssl_path);
            }

            $result = curl_exec($ch);
            if (!$result) {
                $curl_error = curl_error($ch);
				// Use SSL next time, if error occurs.
				if(!$this->ssl_on){
					$this->ssl_on = true;
					$args = func_get_args();
					return $this->sendRequest($args[0], $args[1], $server_timeout);
				}
            }

            curl_close($ch);
        }

        if (!$result) {
            $allow_url_fopen = ini_get('allow_url_fopen');
            if (function_exists('file_get_contents') && isset($allow_url_fopen) && $allow_url_fopen == '1') {
                $opts = array('http' =>
                  array(
                    'method'  => 'POST',
                    'header'  => "Content-Type: text/html\r\n",
                    'content' => $data,
                    'timeout' => $server_timeout
                  )
                );

                $context  = stream_context_create($opts);
                $result = @file_get_contents($url, false, $context);
            }
        }

        if (!$result || !CleantalkHelper::is_json($result)) {
            $response = null;
            $response['errno'] = 1;
            if ($curl_error) {
                $response['errstr'] = sprintf("CURL error: '%s'", $curl_error);
            } else {
                $response['errstr'] = 'No CURL support compiled in';
            }
            $response['errstr'] .= ' or disabled allow_url_fopen in php.ini.';
            $response = json_decode(json_encode($response));

            return $response;
        }

        $errstr = null;
        $response = json_decode($result);
        if ($result !== false && is_object($response)) {
            $response->errno = 0;
            $response->errstr = $errstr;
        } else {
            $errstr = 'Unknown response from ' . $url . '.' . ' ' . $result;

            $response = null;
            $response['errno'] = 1;
            $response['errstr'] = $errstr;
            $response = json_decode(json_encode($response));
        }


        return $response;
    }

    /**
     * httpRequest
     *
     * @param $msg
     *
     * @return CleantalkResponse
     */
    private function httpRequest($msg) {

		// Wiping cleantalk's headers but, not for send_feedback
		if($msg->method_name != 'send_feedback'){

			$ct_tmp = apache_request_headers();

			if(isset($ct_tmp['Cookie']))
				$cookie_name = 'Cookie';
			elseif(isset($ct_tmp['cookie']))
				$cookie_name = 'cookie';
			else
				$cookie_name = 'COOKIE';

			if (isset($ct_tmp[$cookie_name]))
            {
                $ct_tmp[$cookie_name] = preg_replace(array(
                    '/\s?ct_checkjs=[a-z0-9]*[^;]*;?/',
                    '/\s?ct_timezone=.{0,1}\d{1,2}[^;]*;?/',
                    '/\s?ct_pointer_data=.*5D[^;]*;?/',
                    '/\s?apbct_timestamp=\d*[^;]*;?/',
                    '/\s?apbct_site_landing_ts=\d*[^;]*;?/',
                    '/\s?apbct_cookies_test=%7B.*%7D[^;]*;?/',
                    '/\s?apbct_prev_referer=http.*?[^;]*;?/',
                    '/\s?ct_cookies_test=.*?[^;]*;?/',
                    '/\s?ct_ps_timestamp=.*?[^;]*;?/',
                    '/\s?ct_fkp_timestamp=\d*?[^;]*;?/',
                    '/\s?ct_sfw_pass_key=\d*?[^;]*;?/',
                    '/\s?apbct_page_hits=\d*?[^;]*;?/',
                    '/\s?apbct_visible_fields_count=\d*?[^;]*;?/',
                    '/\s?apbct_visible_fields=%7B.*%7D[^;]*;?/',
                ), '', $ct_tmp[$cookie_name]);
                $msg->all_headers = $ct_tmp;
            }
		}

		$msg->all_headers = json_encode($msg->all_headers);

		// Using current server without changing it
        if (false && (!empty($this->work_url) && ($this->server_changed + $this->server_ttl > time()))){

            $url = !empty($this->work_url) ? $this->work_url : $this->server_url;
            $result = $this->sendRequest($msg, $url, $this->server_timeout);

        }else{
			$result = false;
        }

		// Changing server
        if (true || ($result === false || $result->errno != 0)) {

            // Split server url to parts
            preg_match("@^(https?://)([^/:]+)(.*)@i", $this->server_url, $matches);

            $url_prefix = isset($matches[1]) ? $matches[1] : '';
            $url_host   = isset($matches[2]) ? $matches[2] : '';
            $url_suffix = isset($matches[3]) ? $matches[3] : '';

            if (empty($url_host)){

                return false;

            } else if(null !== $servers = $this->get_servers_ip($url_host)) {
                // Loop until find work server
                foreach ($servers as $server) {

                    $this->work_url = $url_prefix . $server['ip'] . $url_suffix;
                    $this->server_ttl = $server['ttl'];

                    $result = $this->sendRequest($msg, $this->work_url, $this->server_timeout);

                    if ($result !== false && $result->errno === 0) {
                        $this->server_change = true;
                        break;
                    }
                }
            } else {
                throw TransportException::fromUrlHostError($url_host);
            }
        }

        $response = new CleantalkResponse(null, $result);

	    $response->sender_ip    = $this->sender_ip;
	    $response->sender_email = $this->sender_email;

        if (!empty($this->data_codepage) && $this->data_codepage !== 'UTF-8') {
            if (!empty($response->comment))
            $response->comment = $this->stringFromUTF8($response->comment, $this->data_codepage);
            if (!empty($response->errstr))
            $response->errstr = $this->stringFromUTF8($response->errstr, $this->data_codepage);
            if (!empty($response->sms_error_text))
            $response->sms_error_text = $this->stringFromUTF8($response->sms_error_text, $this->data_codepage);
        }

        return $response;
    }

    /**
     * Function DNS request
     * @param $host
     * @return array
     */
    private function get_servers_ip($host)
	{
        if (!isset($host))
            return null;

        $servers = array();

		// Get DNS records about URL
        if (function_exists('dns_get_record')) {
            $records = dns_get_record($host, DNS_A);
            if ($records !== FALSE) {
                foreach ($records as $server) {
                    $servers[] = $server;
                }
            }
        }

		// Another try if first failed
        if (count($servers) == 0 && function_exists('gethostbynamel')) {
            $records = gethostbynamel($host);
            if ($records !== FALSE) {
                foreach ($records as $server) {
                    $servers[] = array(
						"ip" => $server,
                        "host" => $host,
                        "ttl" => $this->server_ttl
                    );
                }
            }
        }

		// If couldn't get records
        if (count($servers) == 0){

            $servers[] = array(
				"ip" => null,
                "host" => $host,
                "ttl" => $this->server_ttl
            );

		// If records recieved
        } else {

            $tmp = null;
            $fast_server_found = false;

            foreach ($servers as $server) {

                if ($fast_server_found) {
                    $ping = $this->max_server_timeout;
                } else {
                    $ping = $this->httpPing($server['ip']);
                    $ping = $ping * 1000;
                }

				$tmp[$ping] = $server;

				$fast_server_found = $ping < $this->min_server_timeout ? true : false;

    }

            if (count($tmp)){
                ksort($tmp);
                $response = $tmp;
    }

    }

        return empty($response) ? null : $response;
    }

    /**
    * Function to check response time
    * param string
    * @return int
    */
    private function httpPing($host){

        // Skip localhost ping cause it raise error at fsockopen.
        // And return minimun value
        if ($host == 'localhost')
            return 0.001;

        $starttime = microtime(true);
        $file      = @fsockopen ($host, 80, $errno, $errstr, $this->max_server_timeout/1000);
        $stoptime  = microtime(true);

        if (!$file) {
            $status = $this->max_server_timeout/1000;  // Site is down
        } else {
            fclose($file);
            $status = ($stoptime - $starttime);
            $status = round($status, 4);
        }

        return $status;
    }
}
