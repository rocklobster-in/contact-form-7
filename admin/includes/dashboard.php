<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once ABSPATH . 'wp-admin/includes/dashboard.php';

function wpcf7_dashboard_widgets() {
	return array(
		'wpcf7_dashboard_right_now' => array(
			'widget_name' => __( 'At a Glance', 'contact-form-7' ),
		),
		'wpcf7_dashboard_news' => array(
			'widget_name' => __( 'News', 'contact-form-7' ),
			'context' => 'side',
		),
	);
}

function wpcf7_dashboard_setup() {
	foreach ( wpcf7_dashboard_widgets() as $widget_id => $widget ) {
		wp_add_dashboard_widget(
			$widget_id,
			$widget['widget_name'] ?? '',
			$widget['callback'] ?? $widget_id,
			$widget['control_callback'] ?? null,
			$widget['callback_args'] ?? null,
			$widget['context'] ?? 'normal',
			$widget['priority'] ?? 'core'
		);
	}

	do_action( 'wpcf7_dashboard_setup' );
}

function wpcf7_dashboard_right_now() {
	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'div', array(
		'class' => 'main',
	) );

	$formatter->append_start_tag( 'ul' );

	$formatter->append_start_tag( 'li', array(
		'class' => 'page-count',
	) );

	$formatter->append_start_tag( 'a', array(
		'href' => menu_page_url( 'wpcf7', false ),
	) );

	WPCF7_ContactForm::find();
	$count = WPCF7_ContactForm::count();

	$formatter->append_preformatted(
		esc_html( sprintf(
			/* translators: %s: number of contact forms */
			_n(
				'%s Contact form',
				'%s Contact forms',
				$count,
				'contact-form-7'
			),
			number_format_i18n( $count )
		) )
	);

	if ( current_user_can( 'wpcf7_manage_integration' ) ) {
		$integration = WPCF7_Integration::get_instance();

		$services = array( 'sendinblue', 'turnstile', 'recaptcha', 'stripe' );

		foreach ( $services as $service_name ) {
			if ( $integration->service_exists( $service_name ) ) {
				$service = $integration->get_service( $service_name );

				if ( $service->is_active() ) {
					$formatter->append_start_tag( 'li', array(
						'class' => sprintf(
							'active-integration %s',
							$service_name
						),
					) );

					$setup_page_link = add_query_arg(
						array(
							'service' => $service_name,
							'action' => 'setup',
					 	),
						menu_page_url( 'wpcf7-integration', false )
					);

					$formatter->append_start_tag( 'a', array(
						'href' => $setup_page_link,
					) );

					$formatter->append_preformatted(
						sprintf(
							/* translators: %s: service name */
							__( '<strong>%s</strong> integration is active', 'contact-form-7' ),
							$service->get_title()
						)
					);
				}
			}
		}
	}

	$formatter->end_tag( 'ul' );

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		esc_html( sprintf(
			/* translators: %s: Contact Form 7 version string */
			__( 'Contact Form 7 version %s is running.', 'contact-form-7' ),
			WPCF7_VERSION
		) )
	);

	$formatter->print();
}

function wpcf7_dashboard_news() {
	echo '<div class="cf7com-news hide-if-no-js">';

	wpcf7_dashboard_cf7com_news();

	echo '</div>';
}

function wpcf7_dashboard_cf7com_news() {
	wp_dashboard_cached_rss_widget(
		'wpcf7_dashboard_cf7com_news',
		'wp_dashboard_primary_output',
		array(
			'news' => array(
				'link' => 'https://contactform7.com/',
				'url' => 'https://contactform7.com/feed/',
				'title' => __( 'Contact Form 7 official site', 'contact-form-7' ),
				'items' => 10,
				'show_summary' => 0,
				'show_author' => 0,
				'show_date' => 1,
			),
		)
	);
}
