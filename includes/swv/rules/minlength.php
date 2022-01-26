<?php

class WPCF7_SWV_MinLengthRule extends WPCF7_SWV_Rule {

	const rule_name = 'minlength';

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		$input = (array) $this->get_input( $context );
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		$threshold = (int) $this->properties['threshold'];

		if ( $threshold <= $total ) {
			return true;
		} else {
			return $this->error( 'wpcf7_invalid_minlength' );
		}
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
