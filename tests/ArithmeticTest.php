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
        self::assertEquals(2 * 3, Calculator::evaluate('2 * 3'));
        self::assertEquals(2 / 3, Calculator::evaluate('2 / 3'));
    }

    public function testDivByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);

        Calculator::evaluate('1/0');
    }

    public function testPriorityAndBrackets(): void
    {
        self::assertEquals(6, Calculator::evaluate('2 + 2 * 2'));
        self::assertEquals(8, Calculator::evaluate('(2 + 2) * 2'));
        self::assertEquals(6, Calculator::evaluate('2 + (2 * 2)'));
    }

    public function testUnaryOperators(): void
    {
        self::assertEquals(2, Calculator::evaluate('2'));
        self::assertEquals(5, Calculator::evaluate('+(2+3)'));
        self::assertEquals(-2, Calculator::evaluate('-2'));
        self::assertEquals(-5, Calculator::evaluate('-(2+3)'));
    }
}
