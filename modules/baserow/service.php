<?php

if (!class_exists('WPCF7_Service')) {
    return;
}

class WPCF7_Baserow extends WPCF7_Service
{
    use WPCF7_Baserow_API;

    private static $instance;
    private $token;
    private $url;

    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        $option = WPCF7::get_option('baserow');

        if (isset($option['token'])) {
            $this->token = $option['token'];
        }

        if (isset($option['url'])) {
            $this->url = $option['url'];
        }

    }

    public function get_title()
    {
        return __('Baserow', 'contact-form-7');
    }

    public function is_active()
    {
        return ($this->get_token() && $this->get_url());
    }

    public function get_token()
    {
        return $this->token;
    }

    public function get_url()
    {
        return $this->url;
    }

    public function get_categories()
    {
        return array('email_marketing');
    }

    public function icon()
    {
    }

    public function link()
    {
        echo wpcf7_link(
            'https://github.com/bram2w/baserow',
            'https://github.com/bram2w/baserow'
        );
    }

    protected function log($url, $request, $response)
    {
        wpcf7_log_remote_request($url, $request, $response);
    }

    protected function menu_page_url($args = '')
    {
        $args = wp_parse_args($args, array());

        $url = menu_page_url('wpcf7-integration', false);
        $url = add_query_arg(array('service' => 'baserow'), $url);

        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }

        return $url;
    }

    protected function save_data()
    {
        WPCF7::update_option('baserow', array(
            'token' => $this->token,
            'url' => $this->url
        ));
    }

    protected function reset_data()
    {
        $this->token = null;
        $this->url = null;
        $this->save_data();
    }

    public function load($action = '')
    {
        if ('setup' == $action and 'POST' == $_SERVER['REQUEST_METHOD']) {
            check_admin_referer('wpcf7-baserow-setup');

            if (!empty($_POST['reset'])) {
                $this->reset_data();
                $redirect_to = $this->menu_page_url('action=setup');
            } else {
                $this->token = isset($_POST['token'])
                    ? trim($_POST['token'])
                    : '';

                $this->url = isset($_POST['url'])
                    ? trim($_POST['url'])
                    : '';

                $confirmed = $this->confirm_key();

                if (true === $confirmed) {
                    $redirect_to = $this->menu_page_url(array(
                        'message' => 'success',
                    ));

                    $this->save_data();
                } elseif (false === $confirmed) {
                    $redirect_to = $this->menu_page_url(array(
                        'action' => 'setup',
                        'message' => 'unauthorized',
                    ));
                } else {
                    $redirect_to = $this->menu_page_url(array(
                        'action' => 'setup',
                        'message' => 'invalid',
                    ));
                }
            }

            wp_safe_redirect($redirect_to);
            exit();
        }
    }

    public function admin_notice($message = '')
    {
        if ('unauthorized' == $message) {
            echo sprintf(
                '<div class="notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html(__("Error", 'contact-form-7')),
                esc_html(__("You have not been authenticated. Make sure the provided API Token is correct.", 'contact-form-7'))
            );
        }

        if ('invalid' == $message) {
            echo sprintf(
                '<div class="notice notice-error"><p><strong>%1$s</strong>: %2$s</p></div>',
                esc_html(__("Error", 'contact-form-7')),
                esc_html(__("Invalid key values.", 'contact-form-7'))
            );
        }

        if ('success' == $message) {
            echo sprintf(
                '<div class="notice notice-success"><p>%s</p></div>',
                esc_html(__('Settings saved.', 'contact-form-7'))
            );
        }
    }

    public function display($action = '')
    {

        echo sprintf(
            '<p>%s</p>',
            esc_html(__("Baserow", 'contact-form-7'))
        );

        echo sprintf(
            '<p><strong>%s</strong></p>',
            wpcf7_link(
                __('https://github.com/bram2w/baserow', 'contact-form-7'),
                __('Baserow integration', 'contact-form-7')
            )
        );

        if ($this->is_active()) {
            echo sprintf(
                '<p class="dashicons-before dashicons-yes">%s</p>',
                esc_html(__("Baserow is active on this site.", 'contact-form-7'))
            );
        }

        if ('setup' == $action) {
            $this->display_setup();
        } else {
            echo sprintf(
                '<p><a href="%1$s" class="button">%2$s</a></p>',
                esc_url($this->menu_page_url('action=setup')),
                esc_html(__('Setup integration', 'contact-form-7'))
            );
        }
    }

    private function display_setup()
    {
        $token = $this->get_token();
        $url = $this->get_url();

        ?>
        <form method="post" action="<?php echo esc_url($this->menu_page_url('action=setup')); ?>">
            <?php wp_nonce_field('wpcf7-baserow-setup'); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="publishable"><?php echo esc_html(__('API Token', 'contact-form-7')); ?></label></th>
                    <td><?php
                        if ($this->is_active()) {
                            echo esc_html($token);
                            echo sprintf(
                                '<input type="hidden" value="%s" id="token" name="token" />',
                                esc_attr($token)
                            );
                        } else {
                            echo sprintf(
                                '<input type="text" aria-required="true" value="%s" id="token" name="token" class="regular-text code" />',
                                esc_attr($token)
                            );
                        }
                        ?></td>
                </tr>

                <tr>
                    <th scope="row"><label
                                for="publishable"><?php echo esc_html(__('URL', 'contact-form-7')); ?></label></th>
                    <td><?php
                        if ($this->is_active()) {
                            echo esc_html($url);
                            echo sprintf(
                                '<input type="hidden" value="%s" id="url" name="url" />',
                                esc_attr($url)
                            );
                        } else {
                            echo sprintf(
                                '<input type="text" aria-required="true" value="%s" id="url" name="url" class="regular-text code" />',
                                esc_attr($url)
                            );
                        }
                        ?></td>
                </tr>

                </tbody>
            </table>
            <?php
            if ($this->is_active()) {
                submit_button(
                    _x('Remove key', 'API keys', 'contact-form-7'),
                    'small', 'reset'
                );
            } else {
                submit_button(__('Save changes', 'contact-form-7'));
            }
            ?>
        </form>
        <?php
    }
}


/**
 * Trait for the Baserow API (v3).
 *
 * @link https://github.com/bram2w/baserow
 */
trait WPCF7_Baserow_API
{

    public function confirm_key()
    {
        $endpoint = $this->url;

        $request = array(
            'headers' => array(
                'Authorization' => "Token " . $this->token
            ),
        );

        $response = wp_remote_get($endpoint, $request);
        $response_code = (int)wp_remote_retrieve_response_code($response);

        if (200 === $response_code) { // 200 OK
            return true;
        } elseif (401 === $response_code) { // 401 Unauthorized
            return false;
        } elseif (400 <= $response_code) {
            if (WP_DEBUG) {
                $this->log($endpoint, $request, $response);
            }
        }
    }


    public function get_database_fields($database_id)
    {

        if (!$database_id) {
            return false;
        }

        $request = array(
            'headers' => array(
                'Authorization' => "Token " . $this->token
            ),
        );

        $endpoint = $this->url . '/database/fields/table/' . $database_id . '/';

        $response = wp_remote_get($endpoint, $request);
        $response_code = (int)wp_remote_retrieve_response_code($response);

        if (200 === $response_code) { // 200 OK
            $response_body = wp_remote_retrieve_body($response);
            $response_body = json_decode($response_body, true);

            return (array)$response_body;

        } elseif (400 <= $response_code) {
            if (WP_DEBUG) {
                $this->log($endpoint, $request, $response);
            }
        }

    }

    public function create_row($database_id, $data)
    {
        if (!$database_id) {
            return false;
        }

        $request = array(
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => "Token " . $this->token
            ),
            'body' => json_encode($data),
        );

        $endpoint = $this->url . '/database/rows/table/' . $database_id . '/?user_field_names=true';

        $response = wp_remote_post($endpoint, $request);
        $response_code = (int)wp_remote_retrieve_response_code($response);


        if (in_array($response_code, array(201, 204, 200), true)) {
            $row = wp_remote_retrieve_body($response);
            return $row;
        } elseif (400 <= $response_code) {
            if (WP_DEBUG) {
                $this->log($endpoint, $request, $response);
            }
        }

        return false;
    }
}
