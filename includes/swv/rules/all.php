<?php

class WPCF7_SWV_AllRule extends WPCF7_SWV_CompositeRule {

	const rule_name = 'all';

	public function matches( $context ) {
		if ( false === parent::matches( $context ) ) {
			return false;
		}

		return true;
	}

	public function validate( $context ) {
		foreach ( $this->rules() as $rule ) {
			if ( $rule->matches( $context ) ) {
				$result = $rule->validate( $context );

				if ( is_wp_error( $result ) ) {
					return $result;
				}

				if ( ! $result ) {
					return $this->create_error();
				}
			}
		}

		return true;
	}

}
