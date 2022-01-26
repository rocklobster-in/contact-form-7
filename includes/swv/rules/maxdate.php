<?php

class WPCF7_SWV_MaxDateRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxdate';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
