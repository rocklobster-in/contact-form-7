<?php

add_filter(
    'wpcf7_pre_construct_contact_form_properties',
    'wpcf7_baserow_register_property',
    10, 2
);

/**
 * Registers the baserow contact form property.
 */
function wpcf7_baserow_register_property($properties, $contact_form)
{
    $service = WPCF7_Baserow::get_instance();

    if ($service->is_active()) {
        $properties += array(
            'baserow' => array(),
        );
    }

    return $properties;
}


add_action(
    'wpcf7_save_contact_form',
    'wpcf7_baserow_save_contact_form',
    10, 3
);

/**
 * Saves the baserow property value.
 */
function wpcf7_baserow_save_contact_form($contact_form, $args, $context)
{
    $service = WPCF7_Baserow::get_instance();

    if (!$service->is_active()) {
        return;
    }

    $prop = isset($_POST['wpcf7-baserow'])
        ? (array)$_POST['wpcf7-baserow']
        : array();

    $prop = wp_parse_args(
        $prop,
        array(
            'enable_contact_list' => false,
            'contact_lists' => array(),
            'enable_transactional_email' => false,
            'email_template' => 0,
            'database_id' => 0
        )
    );


    $prop['contact_lists'] = array_map('absint', $prop['contact_lists']);

    $prop['email_template'] = absint($prop['email_template']);
    $prop['database_id'] = absint($prop['database_id']);

    $contact_form->set_properties(array(
        'baserow' => $prop,
    ));
}


add_filter(
    'wpcf7_editor_panels',
    'wpcf7_baserow_editor_panels',
    10, 1
);

/**
 * Builds the editor panel for the baserow property.
 */
function wpcf7_baserow_editor_panels($panels)
{
    $service = WPCF7_Baserow::get_instance();

    if (!$service->is_active()) {
        return $panels;
    }

    $contact_form = WPCF7_ContactForm::get_current();

    echo "<pre>";
    var_dump($contact_form->scan_form_tags()); die();

    $prop = wp_parse_args(
        $contact_form->prop('baserow'),
        array(
            'enable_contact_list' => false,
            'contact_lists' => array(),
            'enable_transactional_email' => false,
            'email_template' => 0,
            'database_id' => 0,
        )
    );

    $editor_panel = function () use ($prop, $service) {

        $description = sprintf(
            esc_html(
                __("You can set up the Baserow integration here. For details, see %s.", 'contact-form-7')
            ),
            wpcf7_link(
                __('https://contactform7.com/baserow-integration/', 'contact-form-7'),
                __('Baserow integration', 'contact-form-7')
            )
        );



        $lists = $service->get_lists();
        $templates = $service->get_templates();

        $database_fields = $service->get_database_fields($prop['database_id']);

        ?>
        <h2><?php echo esc_html(__('Baserow', 'contact-form-7')); ?></h2>

        <fieldset>
            <legend><?php echo $description; ?></legend>

            <table class="form-table" role="presentation">
                <tbody>
                <tr class="<?php echo $prop['enable_contact_list'] ? '' : 'inactive'; ?>">
                    <th scope="row">
                        <?php

                        echo esc_html(__('Contact lists', 'contact-form-7'));

                        ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <?php

                                echo esc_html(__('Contact lists', 'contact-form-7'));

                                ?>
                            </legend>
                            <label for="wpcf7-baserow-enable-contact-list">
                                <input type="checkbox" name="wpcf7-baserow[enable_contact_list]"
                                       id="wpcf7-baserow-enable-contact-list"
                                       value="1" <?php checked($prop['enable_contact_list']); ?> />
                                <?php

                                echo esc_html(
                                    __("Add form submitters to your contact lists", 'contact-form-7')
                                );

                                ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <fieldset>
                            <?php

                            if ($lists) {
                                echo sprintf(
                                    '<legend>%1$s</legend>',
                                    esc_html(__('Select lists to which contacts are added:', 'contact-form-7'))
                                );

                                echo '<ul>';

                                foreach ($lists as $list) {
                                    echo sprintf(
                                        '<li><label><input %1$s /> %2$s</label></li>',
                                        wpcf7_format_atts(array(
                                            'type' => 'checkbox',
                                            'name' => 'wpcf7-baserow[contact_lists][]',
                                            'value' => $list['id'],
                                            'checked' => in_array($list['id'], $prop['contact_lists']),
                                        )),
                                        esc_html($list['name'])
                                    );
                                }

                                echo '</ul>';
                            } else {
                                echo sprintf(
                                    '<legend>%1$s</legend>',
                                    esc_html(__('You have no contact list yet.', 'contact-form-7'))
                                );
                            }

                            ?>
                        </fieldset>
                        <?php

                        echo sprintf(
                            '<p><a %1$s>%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
                            wpcf7_format_atts(array(
                                'href' => 'https://my.baserow.com/lists',
                                'target' => '_blank',
                                'rel' => 'external noreferrer noopener',
                            )),
                            esc_html(__('Manage your contact lists', 'contact-form-7')),
                            esc_html(__('(opens in a new tab)', 'contact-form-7'))
                        );

                        ?>
                    </td>
                </tr>
                <tr class="<?php echo $prop['enable_transactional_email'] ? '' : 'inactive'; ?>">
                    <th scope="row">
                        <?php

                        echo esc_html(__('Welcome email', 'contact-form-7'));

                        ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <?php

                                echo esc_html(__('Welcome email', 'contact-form-7'));

                                ?>
                            </legend>
                            <label for="wpcf7-baserow-enable-transactional-email">
                                <input type="checkbox" name="wpcf7-baserow[enable_transactional_email]"
                                       id="wpcf7-baserow-enable-transactional-email"
                                       value="1" <?php checked($prop['enable_transactional_email']); ?> />
                                <?php

                                echo esc_html(
                                    __("Send a welcome email to new contacts", 'contact-form-7')
                                );

                                ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <fieldset>
                            <?php

                            if ($templates) {
                                echo sprintf(
                                    '<legend>%1$s</legend>',
                                    esc_html(__('Select an email template:', 'contact-form-7'))
                                );

                                echo '<select name="wpcf7-baserow[email_template]">';

                                echo sprintf(
                                    '<option %1$s>%2$s</option>',
                                    wpcf7_format_atts(array(
                                        'value' => 0,
                                        'selected' => 0 === $prop['email_template'],
                                    )),
                                    esc_html(__('&mdash; Select &mdash;', 'contact-form-7'))
                                );

                                foreach ($templates as $template) {
                                    echo sprintf(
                                        '<option %1$s>%2$s</option>',
                                        wpcf7_format_atts(array(
                                            'value' => $template['id'],
                                            'selected' => $prop['email_template'] === $template['id'],
                                        )),
                                        esc_html($template['name'])
                                    );
                                }

                                echo '</select>';
                            } else {
                                echo sprintf(
                                    '<legend>%1$s</legend>',
                                    esc_html(__('You have no active email template yet.', 'contact-form-7'))
                                );
                            }

                            ?>
                        </fieldset>
                        <?php

                        echo sprintf(
                            '<p><a %1$s>%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
                            wpcf7_format_atts(array(
                                'href' => 'https://my.baserow.com/camp/lists/template',
                                'target' => '_blank',
                                'rel' => 'external noreferrer noopener',
                            )),
                            esc_html(__('Manage your email templates', 'contact-form-7')),
                            esc_html(__('(opens in a new tab)', 'contact-form-7'))
                        );

                        ?>
                    </td>
                </tr>

                <tr class="<?php echo $prop['database_id'] ? '0' : '..'; ?>">
                    <th scope="row">
                        <?php

                        echo esc_html(__('Database ID', 'contact-form-7'));

                        ?>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text">
                                <?php

                                echo esc_html(__('Database ID', 'contact-form-7'));

                                ?>
                            </legend>
                            <label for="wpcf7-baserow-database-id">
                                <input type="text" name="wpcf7-baserow[database_id]"
                                       id="wpcf7-baserow-database_id"
                                       value="<?php echo $prop['database_id']; ?>"/>

                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <fieldset>
                            <pre>
                            <?php
                            var_dump($database_fields);
                            ?>
                        </pre>
                        </fieldset>


                    </td>
                </tr>


                </tbody>
            </table>
        </fieldset>
        <?php
    };

    $panels += array(
        'baserow-panel' => array(
            'title' => __('Baserow', 'contact-form-7'),
            'callback' => $editor_panel,
        ),
    );

    return $panels;
}
