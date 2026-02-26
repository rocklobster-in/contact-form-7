<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class StepNumberRule extends AbstractRule {

	const RULE_NAME = 'stepnumber';


	/**
	 * Rule properties.
	 */
	public readonly string $field;
	public readonly string $error;
	public readonly string $base;
	public readonly string $interval;


	/**
	 * Constructor.
	 *
	 * @param array $properties Rule properties.
	 */
	public function __construct( array $properties = [] ) {
		$this->field = $properties[ 'field' ] ?? '';
		$this->error = $properties[ 'error' ] ?? '';
		$this->base = $properties[ 'base' ] ?? '';
		$this->interval = $properties[ 'interval' ] ?? '';
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

		$base = floatval( $this->base );
		$interval = floatval( $this->interval );

		if ( ! ( 0 < $interval ) ) {
			return true;
		}

		foreach ( $values as $value ) {
			$remainder = fmod( floatval( $value ) - $base, $interval );

			if (
				0.0 === round( abs( $remainder ), 6 ) or
				0.0 === round( abs( $remainder - $interval ), 6 )
			) {
				continue;
			}

			throw new Invalidity( $this );
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
			'base' => $this->base,
			'interval' => $this->interval,
		];
	}

}
