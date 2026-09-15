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
		'wpcf7_get_support' => array(
			'widget_name' => __( 'Get Support', 'contact-form-7' ),
		),
		'wpcf7_spam_protection' => array(
			'widget_name' => __( 'Spam Protection', 'contact-form-7' ),
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

function wpcf7_get_support() {
	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		__( 'Do you need help? These support channels are available:', 'contact-form-7' )
	);

	$formatter->append_start_tag( 'ul' );

	$formatter->append_start_tag( 'li', array(
		'class' => 'public',
	) );

	$formatter->append_start_tag( 'a', array(
		'href' => __( 'https://wordpress.org/support/plugin/contact-form-7/', 'contact-form-7' ),
	) );

	$formatter->append_preformatted(
		__( 'Public support forum on WordPress.org', 'contact-form-7' )
	);

	$formatter->append_start_tag( 'li', array(
		'class' => 'private',
	) );

	$formatter->append_start_tag( 'a', array(
		'href' => 'https://contactform7.com/custom-development/',
	) );

	$formatter->append_preformatted(
		__( 'Private support by Codeable experts', 'contact-form-7' )
	);

	$formatter->end_tag( 'ul' );

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		sprintf(
			/* translators: 1: URL to the docs page, 2: URL to the FAQ page */
			__( 'Or, consult the <a href="%1$s">docs</a> and <a href="%2$s">FAQ</a>.', 'contact-form-7' ),
			'https://contactform7.com/docs/',
			'https://contactform7.com/faq/'
		)
	);

	$formatter->print();
}

function wpcf7_spam_protection() {
	$formatter = new WPCF7_HTMLFormatter();

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		__( 'Spammers target everything. Your contact forms are not an exception.', 'contact-form-7' )
	);

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		sprintf(
			/* translators: 1: URL to the Turnstile doc page, 2: URL to the Akismet doc page */
			__( 'To provide effective protection, Contact Form 7 recommends using <a href="%1$s"><strong>Turnstile</strong></a> (good at blocking bots) and <a href="%2$s"><strong>Akismet</strong></a> (good at blocking human spammers) together.', 'contact-form-7' ),
			'https://contactform7.com/turnstile-integration/',
			'https://contactform7.com/spam-filtering-with-akismet/'
		)
	);

	$formatter->append_start_tag( 'p' );

	$formatter->append_preformatted(
		sprintf(
			/* translators: %s: URL to the Disallowed list doc page */
			__( 'You can also block submissions that contain specific keywords or come from specific IP addresses (<a href="%s"><strong>Disallowed list</strong></a>).', 'contact-form-7' ),
			'https://contactform7.com/comment-blacklist/'
		)
	);

	$formatter->print();
}
