<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class EnumRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\EnumRule";

    public static function validValueProvider(): array
    {
        return [
            "blank" => ["", ["a", "b", "c"]],
            "valid" => ["c", ["a", "b", "c"]],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "invalid" => ["z", ["a", "b", "c"]],
        ];
    }

    #[DataProvider("validValueProvider")]
    public function testValidity($field_value, $accept): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "accept" => $accept,
        ]);

        $form_data = new FormDataTree([
            "post" => [
                "the-field-name" => $field_value,
            ],
        ]);

        $result = false;

        try {
            $result = $rule->validate($form_data);
        } catch (InvalidityException $error) {
        }

        $this->assertTrue($result);
    }

    #[DataProvider("invalidValueProvider")]
    public function testInvalidity($field_value, $accept): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "accept" => $accept,
            "error" => "Just another error message.",
        ]);

        $form_data = new FormDataTree([
            "post" => [
                "the-field-name" => $field_value,
            ],
        ]);

        $this->expectException(InvalidityException::class);
        $this->expectExceptionMessage("Just another error message.");

        $rule->validate($form_data);
    }
}
