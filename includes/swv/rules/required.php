<?php

class WPCF7_SWV_RequiredRule extends WPCF7_SWV_Rule {

	const rule_name = 'required';

	public function match( $context ) {
		if ( false === parent::match( $context ) ) {
			return false;
		}

		if ( empty( $context['text'] ) and empty( $context['file'] ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		$input = $this->get_input( $context );

		if ( 'file' === $context ) {
			$input = isset( $input['tmp_name'] ) ? $input['tmp_name'] : '';
		}

		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			return $this->error( 'wpcf7_invalid_required' );
		}

		return true;
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
