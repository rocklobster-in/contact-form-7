<?php

use PHPUnit\Framework\TestCase;
use RockLobsterInc\FormDataTree\FormDataTree;
use RockLobsterInc\Swv\InvalidityException;
use RockLobsterInc\Swv\Rules\{AllRule, RequiredRule};

final class AllRuleTest extends TestCase
{
    public function testValidity(): void
    {
        $rule = new AllRule();

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
                "field-1" => "1",
                "field-2" => "1",
                "field-3" => "1",
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
        $rule = new AllRule();

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
                "field-1" => "1",
                "field-2" => "",
                "field-3" => "1",
            ],
        ]);

        $this->expectException(InvalidityException::class);
        $this->expectExceptionMessage("Error in field-2.");

        $rule->validate($form_data, ["text" => true]);
    }
}
