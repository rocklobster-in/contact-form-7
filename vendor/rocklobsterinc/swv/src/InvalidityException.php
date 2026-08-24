<?php

namespace RockLobsterInc\Swv;

/**
 * Exception that represents a validation error.
 */
final class InvalidityException extends \Exception
{
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
    public function __construct(AbstractRule $rule, array $options = [])
    {
        $this->rule = $rule;
        $this->cause = $options["cause"] ?? null;

        parent::__construct($this->getErrorMessage());
    }

    /**
     * Retrieves the validation error message.
     */
    private function getErrorMessage(): string
    {
        if ($this->cause instanceof self) {
            return $this->cause->getMessage();
        }

        return $this->rule->error ?? "";
    }

    /**
     * Retrieves the field name where the validation error occurs.
     */
    public function getField(): string
    {
        if ($this->cause instanceof self) {
            return $this->cause->rule->field ?? "";
        }

        return $this->rule->field ?? "";
    }
}
