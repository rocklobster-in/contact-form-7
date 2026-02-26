<?php

namespace RockLobsterInc\Swv;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };

/**
 * Abstract class that represents the base for all SWV rules.
 */
abstract class AbstractRule {

	/**
	 * Returns true if this rule matches the given context.
	 *
	 * @param array $context Context.
	 */
	public function matches( array $context ): bool {
		return true;
	}


	/**
	 * Validates the form data according to the logic defined by this rule.
	 *
	 * @param FormDataTree $form_data Form data.
	 * @param array $context Optional context.
	 */
	public function validate( FormDataTree $form_data, array $context = [] ) {
		return true;
	}


	/**
	 * Returns an array that represents the rule properties.
	 *
	 * @return array Array of rule properties.
	 */
	public function toArray(): array {
		return [];
	}

}
