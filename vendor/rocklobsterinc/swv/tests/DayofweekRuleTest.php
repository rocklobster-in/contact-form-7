<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class DayofweekRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\DayofweekRule";

    public static function validValueProvider(): array
    {
        return [
            "blank" => ["", [1, 3, 5]],
            "non-date" => ["Monday", [1, 3, 5]],
            "valid" => ["2026-07-27", [1, 3, 5]],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "invalid" => ["2026-07-26", [1, 3, 5]],
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
