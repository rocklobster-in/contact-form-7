<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter(
	'wpcf7_pre_construct_contact_form_properties',
	'wpcf7_sendinblue_register_property',
	10, 2
);

/**
 * Registers the sendinblue contact form property.
 */
function wpcf7_sendinblue_register_property( $properties, $contact_form ) {
	$service = WPCF7_Sendinblue::get_instance();

	if ( $service->is_active() ) {
		$properties += array(
			'sendinblue' => array(),
		);
	}

	return $properties;
}


add_action(
	'wpcf7_save_contact_form',
	'wpcf7_sendinblue_save_contact_form',
	10, 3
);

/**
 * Saves the sendinblue property value.
 */
function wpcf7_sendinblue_save_contact_form( $contact_form, $args, $context ) {
	$service = WPCF7_Sendinblue::get_instance();

	if ( ! $service->is_active() ) {
		return;
	}

	$prop = wp_parse_args(
		(array) wpcf7_superglobal_post( 'wpcf7-sendinblue', array() ),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

	$prop['contact_lists'] = array_map( 'absint', $prop['contact_lists'] );

	$prop['email_template'] = absint( $prop['email_template'] );

	$contact_form->set_properties( array(
		'sendinblue' => $prop,
	) );
}


add_filter(
	'wpcf7_editor_panels',
	'wpcf7_sendinblue_editor_panels',
	10, 1
);

/**
 * Builds the editor panel for the sendinblue property.
 */
function wpcf7_sendinblue_editor_panels( $panels ) {
	$service = WPCF7_Sendinblue::get_instance();

	if ( ! $service->is_active() ) {
		return $panels;
	}

	$editor_panel = static function () {
		$formatter = new WPCF7_HTMLFormatter();

		$formatter->append_start_tag( 'h2' );

		$formatter->append_preformatted(
			esc_html( __( 'Brevo', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'h2' );

		$formatter->append_start_tag( 'fieldset' );

		$formatter->append_start_tag( 'legend' );

		$formatter->append_preformatted( sprintf(
			/* translators: %s: URL to support page about Brevo integration */
			__( 'You can set up the Brevo integration here. For details, see <a href="%s">Brevo integration</a>.', 'contact-form-7' ),
			'https://contactform7.com/sendinblue-integration/'
		) );

		$formatter->end_tag( 'legend' );

		$formatter->append_start_tag( 'table', array(
			'id' => 'wpcf7-sendinblue-editor-table',
			'class' => 'form-table',
			'role' => 'presentation',
		) );

		$formatter->append_start_tag( 'tbody' );

		$formatter->call_user_func( static function () {
			wpcf7_sendinblue_editor_table();
		} );

		$formatter->end_tag( 'table' );

		$formatter->print();
	};

	$panels += array(
		'sendinblue-panel' => array(
			'title' => __( 'Brevo', 'contact-form-7' ),
			'callback' => $editor_panel,
		),
	);

	return $panels;
}


/**
 * Reusable content for the Brevo editor panel.
 */
function wpcf7_sendinblue_editor_table() {
	$contact_form = WPCF7_ContactForm::get_current();

	$prop = wp_parse_args(
		$contact_form->prop( 'sendinblue' ),
		array(
			'enable_contact_list' => false,
			'contact_lists' => array(),
			'enable_transactional_email' => false,
			'email_template' => 0,
		)
	);

	$lists = wpcf7_sendinblue_get_lists();
	$templates = wpcf7_sendinblue_get_templates();

	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'tr', array(
		'class' => $prop['enable_contact_list'] ? '' : 'inactive',
	) );

	$formatter->append_start_tag( 'th', array(
		'scope' => 'row',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Contact lists', 'contact-form-7' ) )
	);

	$formatter->append_start_tag( 'td' );

	$formatter->append_start_tag( 'fieldset' );

	$formatter->append_start_tag( 'legend', array(
		'class' => 'screen-reader-text',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Contact lists', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'legend' );

	$formatter->append_start_tag( 'label', array(
		'for' => 'wpcf7-sendinblue-enable-contact-list',
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'checkbox',
		'name' => 'wpcf7-sendinblue[enable_contact_list]',
		'id' => 'wpcf7-sendinblue-enable-contact-list',
		'value' => '1',
		'checked' => $prop['enable_contact_list'],
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Add form submitters to your contact lists', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'tr' );

	$formatter->append_start_tag( 'tr' );

	$formatter->append_start_tag( 'th', array(
		'scope' => 'row',
	) );

	$formatter->append_start_tag( 'td' );

	$formatter->append_start_tag( 'fieldset' );

	if ( $lists ) {
		$formatter->append_start_tag( 'legend' );

		$formatter->append_preformatted(
			esc_html( __( 'Select lists to which contacts are added:', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'legend' );

		$formatter->append_start_tag( 'ul' );

		foreach ( $lists as $list ) {
			$formatter->append_start_tag( 'li' );
			$formatter->append_start_tag( 'label' );

			$formatter->append_start_tag( 'input', array(
				'type' => 'checkbox',
				'name' => 'wpcf7-sendinblue[contact_lists][]',
				'value' => $list['id'],
				'checked' => in_array( $list['id'], $prop['contact_lists'] ),
			) );

			$formatter->append_whitespace();

			$formatter->append_preformatted( esc_html( $list['name'] ) );

			$formatter->end_tag( 'li' );
		}

		$formatter->end_tag( 'ul' );

	} else {
		$formatter->append_start_tag( 'legend' );

		$formatter->append_preformatted(
			esc_html( __( 'You have no contact list yet.', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'legend' );
	}

	$formatter->end_tag( 'fieldset' );

	$formatter->append_start_tag( 'p' );

	$formatter->append_start_tag( 'a', array(
		'href' => 'https://my.sendinblue.com/lists',
		'target' => '_blank',
		'rel' => 'external noreferrer noopener',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Manage your contact lists', 'contact-form-7' ) )
	);

	$formatter->append_whitespace();

	$formatter->append_start_tag( 'span', array(
		'class' => 'screen-reader-text',
	) );

	$formatter->append_preformatted(
		esc_html( __( '(opens in a new tab)', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'span' );

	$formatter->append_start_tag( 'span', array(
		'aria-hidden' => 'true',
		'class' => 'dashicons dashicons-external',
	) );

	$formatter->end_tag( 'p' );

	$formatter->end_tag( 'tr' );

	$formatter->append_start_tag( 'tr', array(
		'class' => $prop['enable_transactional_email'] ? '' : 'inactive',
	) );

	$formatter->append_start_tag( 'th', array(
		'scope' => 'row',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Welcome email', 'contact-form-7' ) )
	);

	$formatter->append_start_tag( 'td' );

	$formatter->append_start_tag( 'fieldset' );

	$formatter->append_start_tag( 'legend', array(
		'class' => 'screen-reader-text',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Welcome email', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'legend' );

	$formatter->append_start_tag( 'label', array(
		'for' => 'wpcf7-sendinblue-enable-transactional-email',
	) );

	$formatter->append_start_tag( 'input', array(
		'type' => 'checkbox',
		'name' => 'wpcf7-sendinblue[enable_transactional_email]',
		'id' => 'wpcf7-sendinblue-enable-transactional-email',
		'value' => '1',
		'checked' => $prop['enable_transactional_email'],
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Send a welcome email to new contacts', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'fieldset' );

	$formatter->end_tag( 'tr' );

	$formatter->append_start_tag( 'tr' );

	$formatter->append_start_tag( 'th', array(
		'scope' => 'row',
	) );

	$formatter->append_start_tag( 'td' );

	$formatter->append_start_tag( 'fieldset' );

	if ( $templates ) {
		$formatter->append_start_tag( 'legend' );

		$formatter->append_preformatted(
			esc_html( __( 'Select an email template:', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'legend' );

		$formatter->append_start_tag( 'select', array(
			'name' => 'wpcf7-sendinblue[email_template]',
		) );

		$formatter->append_start_tag( 'option', array(
			'value' => 0,
			'selected' => 0 === $prop['email_template'],
		) );

		$formatter->append_preformatted(
			esc_html( __( '&mdash; Select &mdash;', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'option' );

		foreach ( $templates as $template ) {
			$formatter->append_start_tag( 'option', array(
				'value' => $template['id'],
				'selected' => $prop['email_template'] === $template['id'],
			) );

			$formatter->append_preformatted( esc_html( $template['name'] ) );

			$formatter->end_tag( 'option' );
		}

		$formatter->end_tag( 'select' );

	} else {
		$formatter->append_start_tag( 'legend' );

		$formatter->append_preformatted(
			esc_html( __( 'You have no active email template yet.', 'contact-form-7' ) )
		);

		$formatter->end_tag( 'legend' );
	}

	$formatter->end_tag( 'fieldset' );

	$formatter->append_start_tag( 'p' );

	$formatter->append_start_tag( 'a', array(
		'href' => 'https://my.sendinblue.com/camp/lists/template',
		'target' => '_blank',
		'rel' => 'external noreferrer noopener',
	) );

	$formatter->append_preformatted(
		esc_html( __( 'Manage your email templates', 'contact-form-7' ) )
	);

	$formatter->append_whitespace();

	$formatter->append_start_tag( 'span', array(
		'class' => 'screen-reader-text',
	) );

	$formatter->append_preformatted(
		esc_html( __( '(opens in a new tab)', 'contact-form-7' ) )
	);

	$formatter->end_tag( 'span' );

	$formatter->append_start_tag( 'span', array(
		'aria-hidden' => 'true',
		'class' => 'dashicons dashicons-external',
	) );

	$formatter->end_tag( 'p' );

	$formatter->end_tag( 'tr' );

	$formatter->print();
}


/**
 * Retrieves contact lists from Brevo's database.
 */
function wpcf7_sendinblue_get_lists(): array {
	static $lists = array();

	$service = WPCF7_Sendinblue::get_instance();

	if ( ! empty( $lists ) or ! $service->is_active() ) {
		return $lists;
	}

	$lists = get_transient( 'wpcf7_sendinblue_lists' );

	if ( false !== $lists ) {
		return $lists;
	}

	$lists = array();

	$limit = 50;
	$offset = 0;

	while ( count( $lists ) < $limit * 10 ) {
		$lists_next = (array) $service->get_lists( array(
			'limit' => $limit,
			'offset' => $offset,
		) );

		if ( ! empty( $lists_next ) ) {
			$lists = array_merge( $lists, $lists_next );
		}

		if ( count( $lists_next ) < $limit ) {
			break;
		}

		$offset += $limit;
	}

	set_transient(
		'wpcf7_sendinblue_lists',
		$lists,
		12 // 12 * HOUR_IN_SECONDS // Shortened for testing
	);

	return $lists;
}


/**
 * Retrieves email templates from Brevo's database.
 */
function wpcf7_sendinblue_get_templates(): array {
	static $templates = array();

	$service = WPCF7_Sendinblue::get_instance();

	if ( ! empty( $templates ) or ! $service->is_active() ) {
		return $templates;
	}

	$templates = get_transient( 'wpcf7_sendinblue_templates' );

	if ( false !== $templates ) {
		return $templates;
	}

	$templates = array();

	$limit = 50;
	$offset = 0;

	while ( count( $templates ) < $limit * 10 ) {
		$templates_next = (array) $service->get_templates( array(
			'limit' => $limit,
			'offset' => $offset,
		) );

		if ( ! empty( $templates_next ) ) {
			$templates = array_merge( $templates, $templates_next );
		}

		if ( count( $templates_next ) < $limit ) {
			break;
		}

		$offset += $limit;
	}

	set_transient(
		'wpcf7_sendinblue_templates',
		$templates,
		12 // 12 * HOUR_IN_SECONDS // Shortened for testing
	);

	return $templates;
}
