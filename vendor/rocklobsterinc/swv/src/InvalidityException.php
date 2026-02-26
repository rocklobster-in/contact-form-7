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
	 * Constructor.
	 *
	 * @param AbstractRule $rule SWV rule.
	 */
	public function __construct( AbstractRule $rule ) {
		$this->rule = $rule;
		$this->message = $rule->error ?? '';
	}


	/**
	 * Resets the validation error message.
	 *
	 * @param string $message Validation error message.
	 */
	public function setMessage( string $message ) {
		$this->message = $message;
	}

}
