<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$formatter = new WPCF7_HTMLFormatter();

$formatter->append_start_tag( 'div', array(
	'class' => 'wrap',
) );

$formatter->append_start_tag( 'h1' );

$formatter->append_preformatted(
	esc_html( __( 'Contact Form 7 Dashboard', 'contact-form-7' ) )
);

$formatter->end_tag( 'h1' );

$formatter->call_user_func( static function () {
	do_action( 'wpcf7_admin_warnings',
		'wpcf7-dashboard',
		wpcf7_current_action(),
		null
	);

	do_action( 'wpcf7_admin_notices',
		'wpcf7-dashboard',
		wpcf7_current_action(),
		null
	);
} );

$formatter->append_start_tag( 'div', array(
	'id' => 'dashboard-widgets-wrap',
) );

$formatter->call_user_func( 'wp_dashboard' );

$formatter->print();
