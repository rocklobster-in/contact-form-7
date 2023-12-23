<?php

class WPCF7_SWV_AnyRule extends WPCF7_SWV_CompositeRule {

	const rule_name = 'any';

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

				if ( $result and ! is_wp_error( $result ) ) {
					return true;
				}
			}
		}

		return $this->create_error();
	}

}
