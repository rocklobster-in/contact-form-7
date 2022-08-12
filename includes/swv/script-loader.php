<?php

add_action( 'wp_enqueue_scripts', 'wpcf7_swv_enqueue_scripts', 10, 0 );

function wpcf7_swv_enqueue_scripts() {
	$assets = array();
	$asset_file = wpcf7_plugin_path( 'includes/swv/js/index.asset.php' );

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	}

	$assets = wp_parse_args( $assets, array(
		'src' => wpcf7_plugin_url( 'includes/swv/js/index.js' ),
		'dependencies' => array(),
		'version' => '1.0.0',
		'in_footer' => true,
	) );

	wp_enqueue_script( 'swv',
		$assets['src'],
		$assets['dependencies'],
		$assets['version'],
		$assets['in_footer']
	);
}
