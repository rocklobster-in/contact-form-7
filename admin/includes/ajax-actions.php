<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action(
	'wp_ajax_wpcf7-dashboard-widgets',
	'wpcf7_ajax_dashboard_widgets',
	10, 0
);

function wpcf7_ajax_dashboard_widgets() {
	require_once WPCF7_PLUGIN_DIR . '/admin/includes/dashboard.php';

	set_current_screen( 'toplevel_page_wpcf7-dashboard' );

	if ( 'dashboard_primary' === wpcf7_superglobal_get( 'widget' ) ) {
		wpcf7_dashboard_primary();
	}

	wp_die();
}
