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
            'database_id' => 0,
            'mapping' => array()
        )
    );

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

    $prop = wp_parse_args(
        $contact_form->prop('baserow'),
        array(
            'database_id' => 0,
            'mapping' => array()
        )
    );

    $editor_panel = function () use ($prop, $service, $contact_form) {

        $description = sprintf(
            esc_html(
                __("You can set up the Baserow integration here. For details, see %s.", 'contact-form-7')
            ),
            wpcf7_link(
                __('https://github.com/bram2w/baserow', 'contact-form-7'),
                __('Baserow integration', 'contact-form-7')
            )
        );

        $database_fields = $service->get_database_fields($prop['database_id']);

        ?>
        <h2><?php echo esc_html(__('Baserow', 'contact-form-7')); ?></h2>

        <fieldset>
            <legend><?php echo $description; ?></legend>

            <table class="form-table" role="presentation">
                <tbody>

                <tr>
                    <td colspan="2">
                        <h2><?php echo esc_html(__('Baserow Database', 'contact-form-7')); ?></h2>
                        <p>
                            <?php echo esc_html(__('Baserow Database Settings', 'contact-form-7')); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php

                        echo esc_html(__('Baserow Database ID', 'contact-form-7'));

                        ?>
                    </td>
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
                    <td colspan="2">
                        <h2><?php echo esc_html(__('Contact Form Tag -  Baserow Database Field Mapping', 'contact-form-7')); ?></h2>
                        <p>
                            <?php echo esc_html(__('Baserow Database Settings', 'contact-form-7')); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <?php echo esc_html(__('Contact Form Tag', 'contact-form-7')); ?>
                    </th>
                    <th>
                        <?php echo esc_html(__('Baserow Database Field', 'contact-form-7')); ?>
                    </th>
                </tr>

                <?php foreach ($contact_form->scan_form_tags() as $form_tag): ?>
                    <tr>
                        <td>
                            <?php echo esc_html(__($form_tag->name, 'contact-form-7')); ?>
                            (<?php echo esc_html(__($form_tag->raw_name, 'contact-form-7')); ?>)<br>
                            [<?php echo esc_html(__($form_tag->type, 'contact-form-7')); ?>]
                        </td>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <?php
                                    echo esc_html(__('Database ID', 'contact-form-7'));
                                    ?>
                                </legend>


                                <label for="wpcf7-baserow[mapping][<?php echo $form_tag->raw_name ?>]">


                                    <select name="wpcf7-baserow[mapping][<?php echo $form_tag->raw_name ?>]"
                                            id="wpcf7-baserow[mapping][<?php echo $form_tag->raw_name ?>]">
                                        <option value="">-- No Mapping --</option>
                                        <?php foreach ($database_fields as $database_field): ?>
                                            <option
                                                <?php echo $database_field['name'] == $prop['mapping'][$form_tag->raw_name] ? "selected" : "" ?>
                                                    value="<?php echo $database_field['name'] ?>">
                                                <?php echo $database_field['name'] ?>
                                                (<?php echo $database_field['type'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                </label>
                            </fieldset>
                        </td>
                    </tr>

                <?php endforeach; ?>
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
