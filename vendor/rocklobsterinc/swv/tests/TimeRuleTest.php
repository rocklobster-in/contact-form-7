<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class TimeRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\TimeRule";

    public static function validValueProvider(): array
    {
        return [
            "blank" => [""],
            "valid" => ["16:08:12"],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "invalid" => ["invalid time"],
            "non-time" => ["24:60:60"],
        ];
    }

    #[DataProvider("validValueProvider")]
    public function testValidity($field_value): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
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
    public function testInvalidity($field_value): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
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
