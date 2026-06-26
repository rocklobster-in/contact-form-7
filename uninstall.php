<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

function wpcf7_delete_plugin() {
	global $wpdb;

	delete_option( 'wpcf7' );

	$posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type' => 'wpcf7_contact_form',
			'post_status' => 'any',
		)
	);

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DROP TABLE IF EXISTS %i", // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange -- intentional cleanup on uninstall
			$wpdb->prefix . 'contact_form_7'
		)
	);

	return true;
}

if ( ! defined( 'WPCF7_VERSION' ) ) {
	wpcf7_delete_plugin();
}
