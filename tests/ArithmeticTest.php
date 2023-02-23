<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use PHPUnit\Framework\TestCase;

class ArithmeticTest extends TestCase
{
    public function testOperators(): void
    {
        self::assertEquals(2 + 3, Calculator::evaluate('2 + 3'));
        self::assertEquals(2 - 3, Calculator::evaluate('2 - 3'));
    }

    public function testUnaryOperators(): void
    {
        self::assertEquals(2, Calculator::evaluate('2'));
        self::assertEquals(5, Calculator::evaluate('+(2+3)'));
        self::assertEquals(-2, Calculator::evaluate('-2'));
        self::assertEquals(-5, Calculator::evaluate('-(2+3)'));
    }
}
