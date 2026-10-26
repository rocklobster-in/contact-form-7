<?php

use PHPUnit\Framework\TestCase;
use function RockLobsterInc\Functions\{strip_whitespaces};

final class FunctionsTest extends TestCase
{
    public function testStripWhitespaces(): void
    {
        $input = "	　　test\xff  	";

        $this->assertTrue("test�" === strip_whitespaces($input));
    }
}
