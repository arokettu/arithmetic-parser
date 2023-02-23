<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Config\UnaryOperator;
use Arokettu\ArithmeticParser\Config\UnaryPos;
use PHPUnit\Framework\TestCase;

class PlusMinusTest extends TestCase
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

    public function testUnaryCombination(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('¿', fn ($a) => $a * 2, UnaryPos::PREFIX),
            new UnaryOperator('¡', fn ($a) => $a + 2, UnaryPos::PREFIX),
            new UnaryOperator('?', fn ($a) => $a * 2, UnaryPos::POSTFIX),
            new UnaryOperator('!', fn ($a) => $a + 2, UnaryPos::POSTFIX),
        );

        // postfix
        $val = Calculator::evaluate('-2?! + -2!?', $config);
        self::assertEquals(-(2 * 2 + 2) + -((2 + 2) * 2), $val);
    }
}
