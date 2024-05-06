<?php

add_action(
	'init',
	'wpcf7_init_block_editor_assets',
	10, 0
);

function wpcf7_init_block_editor_assets() {
	$assets = array();

	$asset_file = wpcf7_plugin_path(
		'includes/block-editor/index.asset.php'
	);

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	}

	$assets = wp_parse_args( $assets, array(
		'dependencies' => array(
			'react',
			'wp-api-fetch',
			'wp-block-editor',
			'wp-blocks',
			'wp-components',
			'wp-element',
			'wp-i18n',
			'wp-url',
		),
		'version' => WPCF7_VERSION,
	) );

	wp_register_script(
		'contact-form-7-block-editor',
		wpcf7_plugin_url( 'includes/block-editor/index.js' ),
		$assets['dependencies'],
		$assets['version']
	);

	wp_set_script_translations(
		'contact-form-7-block-editor',
		'contact-form-7'
	);

	register_block_type(
		wpcf7_plugin_path( 'includes/block-editor' ),
		array(
			'editor_script_handles' => 'contact-form-7-block-editor',
		)
	);
}
