<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class RequiredFileRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\RequiredFileRule";

    public static function validValueProvider(): array
    {
        $file = new FileMock([
            "name" => "example.png",
            "size" => 1000,
            "temporaryFilePath" => "/temporary/file/path/example.png",
            "error" => UPLOAD_ERR_OK,
        ]);

        return [
            "has-value" => [$file],
        ];
    }

    public static function invalidValueProvider(): array
    {
        return [
            "blank" => [null],
        ];
    }

    #[DataProvider("validValueProvider")]
    public function testValidity($field_value): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
        ]);

        $form_data = new FormDataTree([
            "files" => [
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
            "files" => [
                "the-field-name" => $field_value,
            ],
        ]);

        $this->expectException(InvalidityException::class);
        $this->expectExceptionMessage("Just another error message.");

        $rule->validate($form_data);
    }
}
