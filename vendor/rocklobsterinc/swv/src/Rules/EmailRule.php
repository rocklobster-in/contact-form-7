<?php

namespace RockLobsterInc\Swv\Rules;

use RockLobsterInc\FormDataTree\{ FormDataTreeInterface as FormDataTree };
use RockLobsterInc\Swv\{ AbstractRule, InvalidityException as Invalidity };
use function RockLobsterInc\Functions\{ array_flatten };

final class EmailRule extends AbstractRule {

	const RULE_NAME = 'email';


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
	 * Returns true if the given string is a valid email address. This method
	 * follows WordPress's is_email() implementation.
	 *
	 * @link https://developer.wordpress.org/reference/functions/is_email/
	 *
	 * @param string $value String to check.
	 */
	public static function isEmail( string $value ): bool {
		if ( strlen( $value ) < 6 ) {
			return false;
		}

		if ( false === strpos( $value, '@', 1 ) ) {
			return false;
		}

		list( $local, $domain ) = explode( '@', $value, 2 );

		if ( ! preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) {
			return false;
		}

		if ( preg_match( '/\.{2,}/', $domain ) ) {
			return false;
		}

		if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
			return false;
		}

		$subs = explode( '.', $domain );

		if ( count( $subs ) < 2 ) {
			return false;
		}

		foreach ( $subs as $sub ) {
			if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) {
				return false;
			}

			if ( ! preg_match( '/^[a-z0-9-]+$/i', $sub ) ) {
				return false;
			}
		}

		return true;
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
			if ( ! self::isEmail( $value ) ) {
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
