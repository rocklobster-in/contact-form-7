<?php
/**
 * Controller for front-end requests, scripts, and styles
 */


add_action(
	'parse_request',
	'wpcf7_control_init',
	20, 0
);

/**
 * Handles a submission in non-Ajax mode.
 */
function wpcf7_control_init() {
	if ( WPCF7_Submission::is_restful() ) {
		return;
	}

	if ( isset( $_POST['_wpcf7'] ) ) {
		$contact_form = wpcf7_contact_form( (int) $_POST['_wpcf7'] );

		if ( $contact_form ) {
			$contact_form->submit();
		}
	}
}


/**
 * Registers main scripts.
 */
add_action(
	'wp_default_scripts',
	static function ( WP_Scripts $scripts ) {
		$assets = array();
		$asset_file = wpcf7_plugin_path( 'includes/js/index.asset.php' );

		if ( file_exists( $asset_file ) ) {
			$assets = include( $asset_file );
		}

		$assets = wp_parse_args( $assets, array(
			'dependencies' => array(),
			'version' => WPCF7_VERSION,
		) );

		$scripts->add(
			'contact-form-7',
			wpcf7_plugin_url( 'includes/js/index.js' ),
			array_merge(
				$assets['dependencies'],
				array( 'swv' )
			),
			$assets['version'],
			1 // in_footer
		);

		$scripts->add(
			'contact-form-7-html5-fallback',
			wpcf7_plugin_url( 'includes/js/html5-fallback.js' ),
			array( 'jquery-ui-datepicker' ),
			WPCF7_VERSION,
			1 // in_footer
		);
	},
	10, 1
);


/**
 * Registers main styles.
 */
add_action(
	'wp_default_styles',
	static function ( WP_Styles $styles ) {
		$styles->add(
			'contact-form-7',
			wpcf7_plugin_url( 'includes/css/styles.css' ),
			array(),
			WPCF7_VERSION,
			'all'
		);

		$styles->add(
			'contact-form-7-rtl',
			wpcf7_plugin_url( 'includes/css/styles-rtl.css' ),
			array( 'contact-form-7' ),
			WPCF7_VERSION,
			'all'
		);

		$styles->add(
			'jquery-ui-smoothness',
			wpcf7_plugin_url(
				'includes/js/jquery-ui/themes/smoothness/jquery-ui.min.css'
			),
			array(),
			'1.12.1',
			'screen'
		);
	},
	10, 1
);


/**
 * Enqueues scripts.
 */
function wpcf7_enqueue_scripts() {
	if ( did_action( 'wpcf7_enqueue_scripts' ) ) {
		return;
	}

	wp_enqueue_script( 'contact-form-7' );

	$wpcf7 = array(
		'api' => array(
			'root' => sanitize_url( get_rest_url() ),
			'namespace' => 'contact-form-7/v1',
		),
	);

	if ( defined( 'WP_CACHE' ) and WP_CACHE ) {
		$wpcf7['cached'] = 1;
	}

	wp_localize_script( 'contact-form-7', 'wpcf7', $wpcf7 );

	do_action( 'wpcf7_enqueue_scripts' );
}


/**
 * Returns true if the main script is enqueued.
 */
function wpcf7_script_is() {
	return wp_script_is( 'contact-form-7' );
}


/**
 * Enqueues styles.
 */
function wpcf7_enqueue_styles() {
	if ( did_action( 'wpcf7_enqueue_styles' ) ) {
		return;
	}

	wp_enqueue_style( 'contact-form-7' );

	if ( wpcf7_is_rtl() ) {
		wp_enqueue_style( 'contact-form-7-rtl' );
	}

	do_action( 'wpcf7_enqueue_styles' );
}


/**
 * Returns true if the main stylesheet is enqueued.
 */
function wpcf7_style_is() {
	return wp_style_is( 'contact-form-7' );
}


add_action(
	'wp_enqueue_scripts',
	'wpcf7_html5_fallback',
	20, 0
);

/**
 * Enqueues scripts and styles for the HTML5 fallback.
 */
function wpcf7_html5_fallback() {
	if ( ! wpcf7_support_html5_fallback() ) {
		return;
	}

	if ( wpcf7_script_is() ) {
		wp_enqueue_script( 'contact-form-7-html5-fallback' );
	}

	if ( wpcf7_style_is() ) {
		wp_enqueue_style( 'jquery-ui-smoothness' );
	}
}
