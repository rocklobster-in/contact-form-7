<?php

namespace RockLobsterInc\Swv;

/**
 * Abstract class that represents the base for all composite-type SWV rules.
 */
abstract class CompositeRule extends AbstractRule {

	/**
	 * Sub-rules of the rule.
	 */
	protected array $rules = [];


	/**
	 * Adds a sub-rule to this composite rule.
	 *
	 * @param AbstractRule $rule Sub-rule to be added.
	 */
	public function addRule( AbstractRule $rule ): bool {
		$this->rules[] = $rule;

		return true;
	}


	/**
	 * Returns an iterator of sub-rules.
	 */
	public function rules(): array {
		return $this->rules;
	}

}
