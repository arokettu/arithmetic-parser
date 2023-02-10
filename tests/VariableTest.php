<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\ConfigBuilder;
use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{
    public function testVariable(): void
    {
        // var names are case-insensitive
        self::assertEquals(5, Calculator::evaluate('MyVar1 + 3', myVar1: 2));
    }

    public function testDuplicateVariable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate variable name: MYVAR1');
        // var names are case-insensitive
        Calculator::evaluate('MyVar1 + 3', myVar1: 2, MYVAR1: 3);
    }

    public function testNoPositionalParams(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid variable name: 0');

        Calculator::evaluate('2 + 3', ConfigBuilder::defaultConfig(), 2, 3);
    }

    public function testMissingVar(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Variable MyVar1 is not defined');

        Calculator::evaluate('MyVar1 + 3');
    }
}
