<?php

class WPCF7_SWV_MaxFileSizeRule extends WPCF7_SWV_Rule {

	const rule_name = 'maxfilesize';

	public function validate( $context ) {
	}

	public function to_array() {
		return array( 'rule' => self::rule_name ) + (array) $this->properties;
	}
}
