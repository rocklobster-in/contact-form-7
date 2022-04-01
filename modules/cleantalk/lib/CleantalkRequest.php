<?php
namespace Cleantalk\CF7_Integration;

/**
 * Request class
 */
class CleantalkRequest {

     /**
     *  All http request headers
     * @var string
     */
     public $all_headers = null;

     /**
     *  IP address of connection
     * @var string
     */
     //public $remote_addr = null;

     /**
     *  Last error number
     * @var integer
     */
     public $last_error_no = null;

     /**
     *  Last error time
     * @var integer
     */
     public $last_error_time = null;

     /**
     *  Last error text
     * @var string
     */
     public $last_error_text = null;

    /**
     * User message
     * @var string
     */
    public $message = null;

    /**
     * Post example with last comments
     * @var string
     */
    public $example = null;

    /**
     * Auth key
     * @var string
     */
    public $auth_key = null;

    /**
     * Engine
     * @var string
     */
    public $agent = null;

    /**
     * Is check for stoplist,
     * valid are 0|1
     * @var int
     */
    public $stoplist_check = null;

    /**
     * Language server response,
     * valid are 'en' or 'ru'
     * @var string
     */
    public $response_lang = null;

    /**
     * User IP
     * @var string
     */
    public $sender_ip = null;

    /**
     * User email
     * @var string
     */
    public $sender_email = null;

    /**
     * User nickname
     * @var string
     */
    public $sender_nickname = null;

    /**
     * Sender info JSON string
     * @var string
     */
    public $sender_info = null;

    /**
     * Post info JSON string
     * @var string
     */
    public $post_info = null;

    /**
     * Is allow links, email and icq,
     * valid are 1|0
     * @var int
     */
    public $allow_links = null;

    /**
     * Time form filling
     * @var int
     */
    public $submit_time = null;

    public $x_forwarded_for = '';
    public $x_real_ip = '';

    /**
     * Is enable Java Script,
     * valid are 0|1|2
	 * Status:
	 *  null - JS html code not inserted into phpBB templates
	 *  0 - JS disabled at the client browser
	 *  1 - JS enabled at the client broswer
     * @var int
     */
    public $js_on = null;

    /**
     * user time zone
     * @var string
     */
    public $tz = null;

    /**
     * Feedback string,
     * valid are 'requset_id:(1|0)'
     * @var string
     */
    public $feedback = null;

    /**
     * Phone number
     * @var type
     */
    public $phone = null;

    /**
    * Method name
    * @var string
    */
    public $method_name = 'check_message';

    /**
     * Fill params with constructor
     * @param type $params
     */
    public function __construct($params = null) {
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param => $value) {
                $this->{$param} = $value;
            }
        }
    }

}
