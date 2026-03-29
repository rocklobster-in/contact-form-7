<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class TimeRule extends AbstractRule {

	const RULE_NAME = 'time';


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
	 * Returns true if the given string is a valid time.
	 *
	 * @link https://html.spec.whatwg.org/multipage/input.html#time-state-(type=time)
	 *
	 * @param string $value String to check.
	 */
	public static function isTime( string $value ): bool {
		$time_pattern = '/^([0-9]{2})\:([0-9]{2})(?:\:([0-9]{2}))?$/';

		if ( ! preg_match( $time_pattern, $value, $matches ) ) {
			return false;
		}

		$hour = (int) $matches[1];
		$minute = (int) $matches[2];
		$second = empty( $matches[3] ) ? 0 : (int) $matches[3];

		return (
			0 <= $hour && $hour <= 23 &&
			0 <= $minute && $minute <= 59 &&
			0 <= $second && $second <= 59
		);
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
			if ( ! self::isTime( $value ) ) {
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
