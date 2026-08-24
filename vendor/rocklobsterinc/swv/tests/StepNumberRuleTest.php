<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class StepNumberRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\StepNumberRule";

    public static function validValueProvider(): array
    {
        return [
            "blank" => ["", 1.3, 3],
            "non-number" => ["xxx", 1.3, 3],
            "valid" => ["10.3", 1.3, 3],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "invalid" => ["11.3", 1.3, 3],
        ];
    }

    #[DataProvider("validValueProvider")]
    public function testValidity($field_value, $base, $interval): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "base" => $base,
            "interval" => $interval,
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
    public function testInvalidity($field_value, $base, $interval): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "base" => $base,
            "interval" => $interval,
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
