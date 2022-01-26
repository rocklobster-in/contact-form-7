<?php

class WPCF7_SWV_MaxLengthRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxlength';

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

		if ( $total <= $threshold ) {
			return true;
		} else {
			return $this->error( 'wpcf7_invalid_maxlength' );
		}
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
