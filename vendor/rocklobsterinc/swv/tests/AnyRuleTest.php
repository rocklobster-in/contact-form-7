<?php

use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;
use RockLobsterInc\Swv\Rules\{AnyRule, RequiredRule};

final class AnyRuleTest extends TestCase
{
    public function testValidity(): void
    {
        $rule = new AnyRule();

        $rule->addRule(
            new RequiredRule([
                "field" => "field-1",
                "error" => "Error in field-1.",
            ]),
        );

        $rule->addRule(
            new RequiredRule([
                "field" => "field-2",
                "error" => "Error in field-2.",
            ]),
        );

        $rule->addRule(
            new RequiredRule([
                "field" => "field-3",
                "error" => "Error in field-3.",
            ]),
        );

        $form_data = new FormDataTree([
            "post" => [
                "field-1" => "",
                "field-2" => "1",
                "field-3" => "",
            ],
        ]);

        $result = false;

        try {
            $result = $rule->validate($form_data, ["text" => true]);
        } catch (InvalidityException $error) {
        }

        $this->assertTrue($result);
    }

    public function testInvalidity(): void
    {
        $rule = new AnyRule([
            "field" => "field-3",
            "error" => "Just another error message.",
        ]);

        $rule->addRule(
            new RequiredRule([
                "field" => "field-1",
                "error" => "Error in field-1.",
            ]),
        );

        $rule->addRule(
            new RequiredRule([
                "field" => "field-2",
                "error" => "Error in field-2.",
            ]),
        );

        $rule->addRule(
            new RequiredRule([
                "field" => "field-3",
                "error" => "Error in field-3.",
            ]),
        );

        $form_data = new FormDataTree([
            "post" => [
                "field-1" => "",
                "field-2" => "",
                "field-3" => "",
            ],
        ]);

        $this->expectException(InvalidityException::class);
        $this->expectExceptionMessage("Just another error message.");

        $rule->validate($form_data, ["text" => true]);
    }
}
