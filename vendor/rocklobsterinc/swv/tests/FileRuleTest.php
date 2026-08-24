<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;

final class FileRuleTest extends TestCase
{
    public static string $ruleClass = "\RockLobsterInc\Swv\Rules\FileRule";

    public static function validValueProvider(): array
    {
        $file = new FileMock([
            "name" => "example.png",
            "size" => 1000,
            "temporaryFilePath" => "/temporary/file/path/example.png",
            "error" => UPLOAD_ERR_OK,
        ]);

        return [
            "blank" => [null, ["image/png"]],
            "valid" => [$file, ["image/png"]],
            "wildcard" => [$file, ["image/*"]],
            "extension" => [$file, [".png"]],
        ];
    }

    public static function invalidValueProvider(): array
    {
        $file = new FileMock([
            "name" => "example.png",
            "size" => 1000,
            "temporaryFilePath" => "/temporary/file/path/example.png",
            "error" => UPLOAD_ERR_OK,
        ]);

        return [
            "invalid" => [$file, ["image/jpeg"]],
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
    public function testInvalidity($field_value, $accept): void
    {
        $rule = new self::$ruleClass([
            "field" => "the-field-name",
            "accept" => $accept,
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
