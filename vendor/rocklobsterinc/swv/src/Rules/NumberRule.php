<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class NumberRule extends AbstractRule {

	const RULE_NAME = 'number';


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
	 * Returns true if the given string is a well-formed number.
	 *
	 * @link https://html.spec.whatwg.org/multipage/input.html#number-state-(type=number)
	 *
	 * @param string $value String to check.
	 */
	public static function isNumber( string $value ): bool {
		$patterns = [
			'/^[-]?[0-9]+(?:[eE][+-]?[0-9]+)?$/',
			'/^[-]?(?:[0-9]+)?[.][0-9]+(?:[eE][+-]?[0-9]+)?$/',
		];

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $value ) ) {
				return true;
			}
		}

		return false;
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

		foreach ( $values as $value ) {
			if ( ! self::isNumber( $value ) ) {
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
		];
	}

}
