<?php
namespace Cleantalk\CF7_Integration;

class CleantalkHelper
{
	private static $cdn_pool = array(
		'cloud_flare' => array(
			'ipv4' => array(
				'103.21.244.0/22',
				'103.22.200.0/22',
				'103.31.4.0/22',
				'104.16.0.0/12',
				'108.162.192.0/18',
				'131.0.72.0/22',
				'141.101.64.0/18',
				'162.158.0.0/15',
				'172.64.0.0/13',
				'173.245.48.0/20',
				'185.93.231.18/20', // User fix
				'185.220.101.46/20', // User fix
				'188.114.96.0/20',
				'190.93.240.0/20',
				'197.234.240.0/22',
				'198.41.128.0/17',
			),
			'ipv6' => array(
				'2400:cb00::/32',
				'2405:8100::/32',
				'2405:b500::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2c0f:f248::/32',
				'2a06:98c0::/29',
			),
		),
	);

	private static $private_networks = array(
		'10.0.0.0/8',
		'100.64.0.0/10',
		'172.16.0.0/12',
		'192.168.0.0/16',
		'127.0.0.1/32',
	);

	/*
	*	Getting arrays of IP (REMOTE_ADDR, X-Forwarded-For, X-Real-Ip, Cf_Connecting_Ip)
	*	reutrns array('remote_addr' => 'val', ['x_forwarded_for' => 'val', ['x_real_ip' => 'val', ['cloud_flare' => 'val']]])
	*/
	static public function ip_get($ips_input = array('real', 'remote_addr', 'x_forwarded_for', 'x_real_ip', 'cloud_flare'), $v4_only = true)
	{
		$ips = array();
		foreach($ips_input as $ip_type){
			$ips[$ip_type] = '';
		} unset($ip_type);

		$headers = apache_request_headers();

		// REMOTE_ADDR
		if(isset($ips['remote_addr'])){
			$ips['remote_addr'] = $_SERVER['REMOTE_ADDR'];
		}

		// X-Forwarded-For
		if(isset($ips['x_forwarded_for'])){
			if(isset($headers['X-Forwarded-For'])){
				$tmp = explode(",", trim($headers['X-Forwarded-For']));
				$ips['x_forwarded_for']= trim($tmp[0]);
			}
		}

		// X-Real-Ip
		if(isset($ips['x_real_ip'])){
			if(isset($headers['X-Real-Ip'])){
				$tmp = explode(",", trim($headers['X-Real-Ip']));
				$ips['x_real_ip']= trim($tmp[0]);
			}
		}

		// Cloud Flare
		if(isset($ips['cloud_flare'])){
			if(isset($headers['Cf-Connecting-Ip'])){
				if(self::ip_mask_match($ips['remote_addr'], self::$cdn_pool['cloud_flare']['ipv4'])){
					$ips['cloud_flare'] = $headers['Cf-Connecting-Ip'];
				}
			}
		}

		// Getting real IP from REMOTE_ADDR or Cf_Connecting_Ip if set or from (X-Forwarded-For, X-Real-Ip) if REMOTE_ADDR is local.
		if(isset($ips['real'])){

			$ips['real'] = $_SERVER['REMOTE_ADDR'];

			// Cloud Flare
			if(isset($headers['Cf-Connecting-Ip'])){
				if(self::ip_mask_match($ips['real'], self::$cdn_pool['cloud_flare']['ipv4'])){
					$ips['real'] = $headers['Cf-Connecting-Ip'];
				}
			// Incapsula proxy
			}elseif(isset($headers['Incap-Client-Ip'])){
				$ips['real'] = $headers['Incap-Client-Ip'];
			// Private networks. Looking for X-Forwarded-For and X-Real-Ip
			}elseif(self::ip_mask_match($ips['real'], self::$private_networks)){
				if(isset($headers['X-Forwarded-For'])){
					$tmp = explode(",", trim($headers['X-Forwarded-For']));
					$ips['real']= trim($tmp[0]);
				}elseif(isset($headers['X-Real-Ip'])){
					$tmp = explode(",", trim($headers['X-Real-Ip']));
					$ips['real']= trim($tmp[0]);
				}
			}
		}

		// Validating IPs
		$result = array();
		foreach($ips as $key => $ip){
			if($v4_only){
				if(self::ip_validate($ip) == 'v4')
					$result[$key] = $ip;
			}else{
				if(self::ip_validate($ip))
					$result[$key] = $ip;
			}
		}

		$result = array_unique($result);

		return count($ips_input) > 1
			? $result
			: (reset($result) !== false
				? reset($result)
				: null);
	}

	/*
	 * Check if the IP belong to mask. Recursivly if array given
	 * @param ip string
	 * @param cird mixed (string|array of strings)
	*/
	static public function ip_mask_match($ip, $cidr){
		if(is_array($cidr)){
			foreach($cidr as $curr_mask){
				if(self::ip_mask_match($ip, $curr_mask)){
					return true;
				}
			} unset($curr_mask);
			return false;
		}
		$exploded = explode ('/', $cidr);
		$net = $exploded[0];
		$mask = 4294967295 << (32 - $exploded[1]);
		return (ip2long($ip) & $mask) == (ip2long($net) & $mask);
	}

	/*
	*	Validating IPv4, IPv6
	*	param (string) $ip
	*	returns (string) 'v4' || (string) 'v6' || (bool) false
	*/
	static public function ip_validate($ip)
	{
		if(!$ip)                                                  return false; // NULL || FALSE || '' || so on...
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) return 'v4';  // IPv4
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) return 'v6';  // IPv6
		                                                          return false; // Unknown
	}

	/**
	 * Function sends raw http request
	 *
	 * May use 4 presets(combining possible):
	 * get_code             - getting only HTTP response code
	 * dont_wait_for_answer - async requests
	 * get                  - GET-request
	 * ssl                  - use SSL
	 *
	 * @param string result
	 * @param string request_method
	 * @return mixed (array || array('error' => true))
	 */
	static public function http__request($url, $data = array(), $presets = null, $opts = array())
	{
		if(function_exists('curl_init')){

			$ch = curl_init();

			// Obligatory options
			$opts = array(
				CURLOPT_URL               => $url,
				CURLOPT_RETURNTRANSFER    => 1,
				CURLOPT_CONNECTTIMEOUT_MS => 3000,
				CURLOPT_FORBID_REUSE      => true,
				CURLOPT_USERAGENT         => 'Cleantalk Antispam ' . (defined('CLEANTALK_AGENT') ? CLEANTALK_AGENT : 'UNKNOWN_AGENT'),
				CURLOPT_POST              => true,
				CURLOPT_POSTFIELDS        => str_replace("&amp;", "&", http_build_query($data)),
				CURLOPT_SSL_VERIFYPEER    => false,
				CURLOPT_SSL_VERIFYHOST    => 0,
				CURLOPT_HTTPHEADER        => array('Expect:'), // Fix for large data and old servers http://php.net/manual/ru/function.curl-setopt.php#82418
			);

			// Use presets
			$presets = is_array($presets) ? $presets : array($presets);
			foreach($presets as $preset){

				switch($preset){

					// Get headers only
					case 'get_code':
						$opts[CURLOPT_HEADER] = true;
						$opts[CURLOPT_NOBODY] = true;
						break;

					// Make a request, don't wait for an answer
					case 'dont_wait_for_answer':
						$opts[CURLOPT_CONNECTTIMEOUT_MS] = 1000;
						$opts[CURLOPT_TIMEOUT_MS] = 500;
						break;

					case 'get':
						$opts[CURLOPT_URL] .= '?'.str_replace("&amp;", "&", http_build_query($data));
						$opts[CURLOPT_POST] = false;
						$opts[CURLOPT_POSTFIELDS] = null;
						break;

					case 'ssl':
						$opts[CURLOPT_SSL_VERIFYPEER] = true;
						$opts[CURLOPT_SSL_VERIFYHOST] = 2;
						break;

					default:

						break;
				}

			} unset($preset);

			curl_setopt_array($ch, $opts);
			$result = @curl_exec($ch);

			if(in_array('dont_wait_for_answer', $presets)) return true;

			if($result){
				$result = explode(PHP_EOL, $result);
				if(in_array('get_code', $presets)) $result = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
				curl_close($ch);
				return $result;
			}else
				$error = array('error' => true, 'error_string' => curl_error($ch));
		}else
			$error = array('error' => true, 'error_string' => 'CURL_NOT_INSTALLED');

		/** Fix for get_code preset */
		if($presets && ($presets == 'get_code' || (is_array($presets) && in_array('get_code', $presets) ) )
			&& (isset($error) && $error['error_string'] == 'CURL_NOT_INSTALLED')
		){
			$headers = get_headers($url);
			$out = (int)preg_replace('/.*(\d{3}).*/', '$1', $headers[0]);
		}

		return $out;
	}

	/**
	* Checks if the string is JSON type
	* @param string
	* @return bool
	*/
	static public function is_json($string)
	{
		return is_string($string) && is_array(json_decode($string, true)) ? true : false;
	}

	/**
	* Function removing non UTF8 characters from array||string
	* @param  mixed(array||string)
	* @return mixed(array||string)
	*/
	static public function removeNonUTF8FromArray($data)
	{
		foreach($data as $key => $val){
			if(is_array($val)){
				$data[$key] = self::removeNonUTF8FromArray($val);
			}else{
				$data[$key] = self::removeNonUTF8FromString($val);
			}
		}
		return $data;
	}

	/**
	* Function removing non UTF8 characters from array||string
	* param  mixed(array||string)
	* return mixed(array||string)
	*/
	public static function removeNonUTF8FromString($data)
	{
		if(!preg_match('//u', $data))
			$data =  'Nulled. Not UTF8 encoded or malformed.';
		return $data;
	}

	/**
	* Function convert array to UTF8 and removes non UTF8 characters
	* param array
	* param string
	* @return array
	*/
	public static function arrayToUTF8($array, $data_codepage = null)
	{
		foreach($array as $key => $val){

			if(is_array($val))
				$array[$key] = self::arrayToUTF8($val, $data_codepage);
			else
				$array[$key] = self::stringToUTF8($val, $data_codepage);
		}
		return $array;
	}

    /**
    * Function convert string to UTF8 and removes non UTF8 characters
    * param string
    * param string
    * @return string
    */
    public static function stringToUTF8($str, $data_codepage = null)
	{
        if (!preg_match('//u', $str) && function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding')){

            if ($data_codepage !== null)
                return mb_convert_encoding($str, 'UTF-8', $data_codepage);

            $encoding = mb_detect_encoding($str);

            if ($encoding)
                return mb_convert_encoding($str, 'UTF-8', $encoding);
        }
        return $str;
    }

    /**
    * Function convert string from UTF8
    * param string
    * param string
    * @return string
    */
    public static function stringFromUTF8($str, $data_codepage = null)
	{
        if(preg_match('u', $str) && function_exists('mb_convert_encoding') && $data_codepage !== null)
            return mb_convert_encoding($str, $data_codepage, 'UTF-8');

        return $str;
	}
}
