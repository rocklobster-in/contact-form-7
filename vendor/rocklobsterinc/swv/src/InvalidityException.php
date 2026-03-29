<?php

namespace RockLobsterInc\Swv;

/**
 * Exception that represents a validation error.
 */
final class InvalidityException extends \Exception {

	/**
	 * The SWV rule who has thrown this error.
	 */
	public readonly AbstractRule $rule;


	/**
	 * The specific cause of this exception.
	 */
	public readonly mixed $cause;


	/**
	 * Constructor.
	 *
	 * @param AbstractRule $rule SWV rule.
	 */
	public function __construct( AbstractRule $rule, array $options = [] ) {
		$this->rule = $rule;
		$this->message = $rule->error ?? '';
		$this->cause = $options[ 'cause' ] ?? null;
	}


	/**
	 * Retrieves the validation error message.
	 */
	public function getMessage() {
		if ( $this->cause instanceof self ) {
			return $this->cause->message;
		} else {
			return $this->message;
		}
	}


	/**
	 * Retrieves the field name where the validation error occurs.
	 */
	public function getField() {
		if ( $this->cause instanceof self ) {
			return $this->cause->rule->field ?? '';
		} else {
			return $this->rule->field ?? '';
		}
	}
}
