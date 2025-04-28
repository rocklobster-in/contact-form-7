<?php

class WPCF7_Filesystem {

	private static $instance;

	private $filesystem;

	public static function get_instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		$this->connect();
	}

	private function connect() {
		global $wp_filesystem;

		if ( $this->filesystem ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		ob_start();
		$credentials = request_filesystem_credentials( '' );
		ob_end_clean();

		if ( false === $credentials or ! WP_Filesystem( $credentials ) ) {
			wp_trigger_error(
				__FUNCTION__,
				__( 'Could not access filesystem.', 'contact-form-7' )
			);
		}

		if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
			$this->filesystem = $wp_filesystem;
		} else {
			$this->filesystem = new WP_Filesystem_Direct( 1 );
		}

		if ( ! defined( 'FS_CHMOD_DIR' ) ) {
			define( 'FS_CHMOD_DIR', fileperms( ABSPATH ) & 0777 | 0755 );
		}

		if ( ! defined( 'FS_CHMOD_FILE' ) ) {
			define( 'FS_CHMOD_FILE', fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 );
		}
	}

	public function delete( $file, $recursive = false, $type = false ) {
		return $this->filesystem->delete( $file, $recursive, $type );
	}
}
