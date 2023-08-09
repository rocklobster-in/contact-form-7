<?php

add_action(
	'wp_default_scripts',
	static function ( WP_Scripts $scripts ) {
		$assets = array();
		$asset_file = wpcf7_plugin_path( 'includes/swv/js/index.asset.php' );

		if ( file_exists( $asset_file ) ) {
			$assets = include( $asset_file );
		}

		$assets = wp_parse_args( $assets, array(
			'dependencies' => array(),
			'version' => WPCF7_VERSION,
		) );

		$scripts->add( 'swv',
			wpcf7_plugin_url( 'includes/swv/js/index.js' ),
			$assets['dependencies'],
			$assets['version'],
			1 // in_footer
		);
	},
	10, 1
);
