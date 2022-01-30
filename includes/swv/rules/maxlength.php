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
		$field = $this->get_property( 'field' );
		$input = isset( $_POST[$field] ) ? $_POST[$field] : '';
		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return true;
		}

		$total = 0;

		foreach ( $input as $i ) {
			$total += wpcf7_count_code_units( $i );
		}

		$threshold = (int) $this->get_property( 'threshold' );

		if ( $total <= $threshold ) {
			return true;
		} else {
			if ( isset( $context['validity'] )
			and $context['validity'] instanceof WPCF7_Validation ) {
				$error = new WP_Error( 'wpcf7_invalid_maxlength',
					$this->get_property( 'message' )
				);

				$context['validity']->invalidate( $field, $error );
			}

			return false;
		}
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
