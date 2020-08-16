<?php

add_action( 'enqueue_block_editor_assets',
	'wpcf7_enqueue_block_editor_assets',
	10, 0
);

function wpcf7_enqueue_block_editor_assets() {
	wp_enqueue_script(
		'wpcf7-block-editor',
		wpcf7_plugin_url( 'includes/js/block-editor.js' ),
		array(
		),
		WPCF7_VERSION
	);
}
