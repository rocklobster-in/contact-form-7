<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once ABSPATH . 'wp-admin/includes/dashboard.php';

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

function wpcf7_dashboard_primary() {
	wp_dashboard_cached_rss_widget(
		'wpcf7_dashboard_primary',
		'wp_dashboard_primary_output',
		array(
			'news' => array(
				'link' => 'https://contactform7.com/',
				'url' => 'https://contactform7.com/feed/',
				'title' => 'WordPress Blog',
				'items' => 2,
				'show_summary' => 0,
				'show_author' => 0,
				'show_date' => 0,
			),
		)
	);
}
