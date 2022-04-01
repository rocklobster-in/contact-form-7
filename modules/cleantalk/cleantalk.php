<?php

/**
 * CleanTalk Anti-Spam module main file
 *
 * @author CleanTalk team https://cleantalk.org
 * @version 1.0.0
 */

wpcf7_include_module_file( 'cleantalk/service.php' );

add_action( 'wpcf7_init', 'wpcf7_cleantalk_register_service', 15, 0 );

/**
 * Registers the CleanTalk Anti-Spam service.
 */
function wpcf7_cleantalk_register_service() {
	$integration = WPCF7_Integration::get_instance();
	$integration->add_service('cleantalk', WPCF7_CLEANTALK::get_instance());
}
