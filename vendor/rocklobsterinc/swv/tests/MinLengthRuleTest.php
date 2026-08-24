<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class MinLengthRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\MinLengthRule";

    public static function validValueProvider(): array
    {
        return [
            "blank" => ["", 6],
            "valid" => ["abc一二三", 6],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "invalid" => ["abc一二", 6],
        ];
    }

    #[DataProvider("validValueProvider")]
    public function testValidity($field_value, $threshold): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "threshold" => $threshold,
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
    public function testInvalidity($field_value, $threshold): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "threshold" => $threshold,
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
