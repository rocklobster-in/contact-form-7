<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ CompositeRule, InvalidityException as Invalidity };

final class AllRule extends CompositeRule {

	const RULE_NAME = 'all';


	/**
	 * Rule properties.
	 */
	public readonly string $field;
	public readonly string $error;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->field = $properties[ 'field' ] ?? '';
		$this->error = $properties[ 'error' ] ?? '';
	}


	/**
	 * Returns true if this rule matches the given context.
	 *
	 * @param array $context Context.
	 */
	public function matches( array $context ): bool {
		if ( false === parent::matches( $context ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Validates the form data according to the logic defined by this rule.
	 *
	 * @param FormDataTree $form_data Form data.
	 * @param array $context Optional context.
	 */
	public function validate( FormDataTree $form_data, array $context = [] ) {
		foreach ( $this->rules() as $rule ) {
			if ( $rule->matches( $context ) ) {
				try {
					$rule->validate( $form_data, $context );
				} catch ( Invalidity $error ) {
					if ( '' === $error->getMessage() ) {
						$error->setMessage( $this->error );
					}

					throw $error;
				}
			}
		}

		return true;
	}


	/**
	 * Returns an array that represents the rule properties.
	 *
	 * @return array Array of rule properties.
	 */
	public function toArray(): array {
		$rules = [];

		foreach ( $this->rules() as $rule ) {
			$rules[] = $rule->toArray();
		}

		return [
			'rule' => self::RULE_NAME,
			'field' => $this->field,
			'error' => $this->error,
			'rules' => $rules,
		];
	}

}
