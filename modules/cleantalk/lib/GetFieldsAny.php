<?php

namespace Cleantalk\CF7_Integration;

class GetFieldsAny
{
    /**
     * @var array
     */
    private $input_array;

    /**
     * Skip fields with these strings and known service fields
     * @var array
     */
    private $skip_fields_with_strings = array(
        // Common
        'ct_checkjs', //Do not send ct_checkjs
        'nonce', //nonce for strings such as 'rsvp_nonce_name'
        'security',
        // 'action',
        'http_referer',
        'referer-page',
        'timestamp',
        'captcha',
        // Formidable Form
        'form_key',
        'submit_entry',
        // Custom Contact Forms
        'form_id',
        'ccf_form',
        'form_page',
        // Qu Forms
        'iphorm_uid',
        'form_url',
        'post_id',
        'iphorm_ajax',
        'iphorm_id',
        // Fast SecureContact Froms
        'fs_postonce_1',
        'fscf_submitted',
        'mailto_id',
        'si_contact_action',
        // Ninja Forms
        'formData_id',
        'formData_settings',
        'formData_fields_\d+_id',
        'formData_fields_\d+_files.*',
        // E_signature
        'recipient_signature',
        'output_\d+_\w{0,2}',
        // Contact Form by Web-Settler protection
        '_formId',
        '_returnLink',
        // Social login and more
        '_save',
        '_facebook',
        '_social',
        'user_login-',
        // Contact Form 7
        '_wpcf7',
        'ebd_settings',
        'ebd_downloads_',
        'ecole_origine',
        'signature',
        // Ultimate Form Builder
        'form_data_%d_name',
    );

    /**
     * Skip request if fields exists
     * @var array
     */
    private $skip_params = array(
        'ipn_track_id',    // PayPal IPN #
        'txn_type',        // PayPal transaction type
        'payment_status',    // PayPal payment status
        'ccbill_ipn',        // CCBill IPN
        'ct_checkjs',        // skip ct_checkjs field
        'api_mode',         // DigiStore-API
        'loadLastCommentId' // Plugin: WP Discuz. ticket_id=5571
    );

    /**
     * Reset $message if we have a sign-up data
     * @var array
     */
    private $skip_message_post = array(
        'edd_action', // Easy Digital Downloads
    );

    /**
     * Fields to replace with ****
     * @var array
     */
    private $obfuscate_params = array(
        'password',
        'pass',
        'pwd',
        'pswd'
    );

    /**
     * If the form checking will be processing
     * @var bool
     */
    private $contact = true;

    /**
     * @var array
     */
    private $visible_fields_arr;

    /**
     * Processed data array to output
     * @var array
     */
    private $processed_data = array(
        'email'    => '',
        'nickname' => array(),
        'subject'  => '',
        'contact'  => true,
        'message'  => array()
    );

    /**
     * @var string
     */
    private $preprocessed_email;

    /**
     * @var string
     */
    private $preprocessed_nickname;

    /**
     * @var string
     */
    private $prev_name = '';

    /**
     * GetFieldsAny constructor.
     *
     * @param array $input_array
     */
    public function __construct(array $input_array)
    {
        $this->input_array = $input_array;
        $this->visible_fields_arr = $this->getVisibleFields();
    }

    /**
     * Public interface to process fields
     *
     * @param string $email
     * @param string $nickname
     *
     * @return array
     */
    public function getFields($email = '', $nickname = '')
    {
        $this->preprocessed_email    = $email;
        $this->preprocessed_nickname = is_string($nickname) ? $nickname : '';

        if (count($this->input_array)) {
            $this->process($this->input_array);
        }

        foreach ($this->skip_message_post as $v) {
            if (isset($_POST[$v])) {
                $this->processed_data['message'] = null;
                break;
            }
        }

        if ( ! $this->contact) {
            $this->processed_data['contact'] = $this->contact;
        }

        if ($this->preprocessed_email) {
            $this->processed_data['email'] = $this->preprocessed_email;
        }

        if ($this->preprocessed_nickname) {
            $this->processed_data['nickname'] = $this->preprocessed_nickname;
        }

        if (is_array($this->processed_data['nickname'])) {
            $nickname_str = '';
            foreach ($this->processed_data['nickname'] as $value) {
                $nickname_str .= ($value ? $value . " " : "");
            }
            $this->processed_data['nickname'] = trim($nickname_str);
        }

        return $this->processed_data;
    }

    /**
     * Makes main logic recursively
     *
     * @param $arr
     */
    private function process($arr)
    {
        foreach ($arr as $key => $value) {
            if (is_string($value)) {
                $tmp = strpos($value, '\\') !== false ? stripslashes($value) : $value;

                # Remove html tags from $value
                $tmp = preg_replace('@<.*?>@', '', $tmp);

                $decoded_json_value = json_decode($tmp, true);       // Try parse JSON from the string
                if (strpos($value, "\n") === false || strpos($value, "\r") === false) {
                    // Parse an only single-lined string
                    parse_str(urldecode($tmp), $decoded_url_value); // Try parse URL from the string
                }

                // If there is "JSON data" set is it as a value
                if ($decoded_json_value !== null) {
                    if (isset($arr['action']) && $arr['action'] === 'nf_ajax_submit') {
                        unset($decoded_json_value['settings']);
                    }

                    $value = $decoded_json_value;
                    // If there is "URL data" set is it as a value
                } elseif (
                    isset($decoded_url_value) &&
                    ! (count($decoded_url_value) === 1 &&
                    reset($decoded_url_value) === '')
                ) {
                    $value = $decoded_url_value;

                    // Ajax Contact Forms. Get data from such strings:
                    // acfw30_name %% Blocked~acfw30_email %% s@cleantalk.org
                    // acfw30_textarea %% msg
                } elseif (preg_match('/^\S+\s%%\s\S+.+$/', $value)) {
                    $value = explode('~', $value);
                    foreach ($value as &$val) {
                        $tmp = explode(' %% ', $val);
                        $val = array($tmp[0] => $tmp[1]);
                    }
                    unset($val);
                }
            }

            if ( ! is_array($value) && ! is_object($value) ) {
                if (
                    (in_array($key, $this->skip_params, true) && $key !== 0 && $key !== '') ||
                    0 === strpos($key, "ct_checkjs")
                ) {
                    $this->contact = false;
                }

                if ($value === '') {
                    continue;
                }

                // Skipping fields names with strings from (array)skip_fields_with_strings
                foreach ($this->skip_fields_with_strings as $needle) {
                    if (preg_match("/" . $needle . "/", $key) === 1) {
                        continue(2);
                    }
                }

                // Obfuscating params
                foreach ($this->obfuscate_params as $needle) {
                    if (strpos($key, $needle) !== false) {
                        $value = $this->obfuscateParam($value);
                    }
                }

                $value_for_email = trim(
                    $this->stripShortcodes($value)
                );    // Removes shortcodes to do better spam filtration on server side.
                // Removes whitespaces
                $value = urldecode(trim($this->stripShortcodes($value))); // Fully cleaned message

                // Email
                if (preg_match("/^\S+@\S+\.\S+$/", $value_for_email) &&
                    (empty($this->visible_fields_arr) ||
                     in_array($key, $this->visible_fields_arr, true))) {
                    // Bypass email collecting if it is set by attribute.
                    if ($this->preprocessed_email) {
                        continue;
                    }
                    $this->processed_data['email'] = $value_for_email;
                    // Names
                } elseif (false !== stripos($key, "name")) {
                    // Bypass name collecting if it is set by attribute or it is on invisible fields.
                    if (
                        $this->preprocessed_nickname &&
                        (empty($this->visible_fields_arr) ||
                         in_array($key, $this->visible_fields_arr, true))
                    ) {
                        continue;
                    }
                    preg_match("/(name.?(your|first|for)|(your|first|for).?name)/", $key, $match_forename);
                    preg_match(
                        "/(name.?(last|family|second|sur)|(last|family|second|sur).?name)/",
                        $key,
                        $match_surname
                    );
                    preg_match("/(name.?(nick|user)|(nick|user).?name)/", $key, $match_nickname);

                    if (count($match_forename) > 1) {
                        $this->processed_data['nickname']['first'] = $value;
                    } elseif (count($match_surname) > 1) {
                        $this->processed_data['nickname']['last'] = $value;
                    } elseif (count($match_nickname) > 1) {
                        $this->processed_data['nickname']['nick'] = $value;
                    } else {
                        $this->processed_data['message'][$this->prev_name . $key] = $value;
                    }
                    // Subject
                } elseif ($this->processed_data['subject'] === '' && false !== stripos($key, "subject")) {
                    $this->processed_data['subject'] = $value;
                    // Message
                } else {
                    $this->processed_data['message'][$this->prev_name . $key] = $value;
                }
            } elseif ( ! is_object($value)) {
                if (empty($value)) {
                    continue;
                }

                $prev_name_original = $this->prev_name;
                $this->prev_name    = ($this->prev_name === '' ? $key . '_' : $this->prev_name . $key . '_');

                $this->process($value);

                $this->prev_name = $prev_name_original;
            }
        }
    }

    /**
     * Get visible fields from Cookies to skip them processing
     *
     * @return array
     * @ToDO Will be implemented at the next releases.
     */
    private function getVisibleFields()
    {
        return array();
    }

    /**
     * Masks a value with asterisks (*)
     *
     * @param null|string $value
     *
     * @return string|null
     */
    private function obfuscateParam($value = null)
    {
        if ($value) {
            $length = strlen($value);
            $value  = str_repeat('*', $length);
        }

        return $value;
    }

    /**
     * WP function placeholder
     *
     * @param $value
     *
     * @return string
     */
    private function stripShortcodes($value)
    {
        if (function_exists('strip_shortcodes')) {
            return strip_shortcodes($value);
        }

        return $value;
    }
}
