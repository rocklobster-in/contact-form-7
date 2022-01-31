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
		$field = $this->get_property( 'field' );

		if ( ! empty( $context['text'] ) ) {
			$input = isset( $_POST[$field] ) ? $_POST[$field] : '';
		} elseif ( ! empty( $context['file'] ) ) {
			$input = isset( $_FILES[$field]['tmp_name'] )
				? $_FILES[$field]['tmp_name'] : '';
		}

		$input = wpcf7_array_flatten( $input );
		$input = wpcf7_exclude_blank( $input );

		if ( empty( $input ) ) {
			$error = new WP_Error( 'wpcf7_invalid_required',
				$this->get_property( 'message' )
			);

			yield $field => $error;
			return false;
		}

		return true;
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
