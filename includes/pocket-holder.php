<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Handy trait provides methods to handle dynamic properties.
 */
trait WPCF7_PocketHolder {

	protected $pocket = array();

	public function pull( $key ) {
		if ( isset( $this->pocket[$key] ) ) {
			return $this->pocket[$key];
		}
	}

	public function push( $key, $value ) {
		$this->pocket[$key] = $value;
	}

}
