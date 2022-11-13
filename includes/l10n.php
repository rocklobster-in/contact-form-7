<?php

function wpcf7_l10n() {
	static $l10n = array();

	if ( ! empty( $l10n ) ) {
		return $l10n;
	}

	if ( ! is_admin() ) {
		return $l10n;
	}

	require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

	$api = translations_api( 'plugins', array(
		'slug' => 'contact-form-7',
		'version' => WPCF7_VERSION,
	) );

	if ( is_wp_error( $api )
	or empty( $api['translations'] ) ) {
		return $l10n;
	}

	foreach ( (array) $api['translations'] as $translation ) {
		if ( ! empty( $translation['language'] )
		and ! empty( $translation['english_name'] ) ) {
			$l10n[$translation['language']] = $translation['english_name'];
		}
	}

	return $l10n;
}

function wpcf7_is_valid_locale( $locale ) {
	$pattern = '/^[a-z]{2,3}(?:_[a-zA-Z_]{2,})?$/';
	return (bool) preg_match( $pattern, $locale );
}

function wpcf7_is_rtl( $locale = '' ) {
	static $rtl_locales = array(
		'ar' => 'Arabic',
		'ary' => 'Moroccan Arabic',
		'azb' => 'South Azerbaijani',
		'fa_IR' => 'Persian',
		'haz' => 'Hazaragi',
		'he_IL' => 'Hebrew',
		'ps' => 'Pashto',
		'ug_CN' => 'Uighur',
	);

	if ( empty( $locale )
	and function_exists( 'is_rtl' ) ) {
		return is_rtl();
	}

	if ( empty( $locale ) ) {
		$locale = determine_locale();
	}

	return isset( $rtl_locales[$locale] );
}

function wpcf7_load_textdomain( $locale = '' ) {
	static $available_locales = null;

	if ( ! isset( $available_locales ) ) {
		$available_locales = array_merge(
			array( 'en_US' ),
			get_available_languages()
		);
	}

	if ( ! in_array( $locale, $available_locales, true ) ) {
		$locale = determine_locale();
	}

	$mofile = path_join(
		WP_LANG_DIR . '/plugins/',
		sprintf( '%s-%s.mo', WPCF7_TEXT_DOMAIN, $locale )
	);

	return load_textdomain( WPCF7_TEXT_DOMAIN, $mofile, $locale );
}

function wpcf7_unload_textdomain( $reloadable = false ) {
	unload_textdomain( WPCF7_TEXT_DOMAIN, $reloadable );
}

function wpcf7_switch_locale( $locale, callable $callback ) {
	$previous_locale = determine_locale();

	if ( $locale !== $previous_locale ) {
		wpcf7_unload_textdomain();
		switch_to_locale( $locale );
		wpcf7_load_textdomain( $locale );
	}

	$result = call_user_func( $callback );

	if ( $locale !== $previous_locale ) {
		wpcf7_unload_textdomain( true );
		restore_current_locale();
		wpcf7_load_textdomain( $previous_locale );
	}

	return $result;
}
