<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten, canonicalize_newline };

final class EnumRule extends AbstractRule {

	const RULE_NAME = 'enum';


	/**
	 * Rule properties.
	 */
	public readonly string $field;
	public readonly string $error;
	public readonly array $accept;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->field = $properties[ 'field' ] ?? '';
		$this->error = $properties[ 'error' ] ?? '';
		$this->accept = $properties[ 'accept' ] ?? [];
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

		if ( empty( $context[ 'text' ] ) ) {
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
		$values = $form_data->getAll( $this->field );
		$values = array_flatten( $values );

		if ( empty( $values ) ) {
			return true;
		}

		$acceptable_values = canonicalize_newline( $this->accept );

		foreach ( $values as $value ) {
			$value = canonicalize_newline( $value );

			if ( ! in_array( $value, $acceptable_values, true ) ) {
				throw new Invalidity( $this, [ 'cause' => $value ] );
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
		return [
			'rule' => self::RULE_NAME,
			'field' => $this->field,
			'error' => $this->error,
			'accept' => $this->accept,
		];
	}

}
