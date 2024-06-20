<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Config\UnaryOperator;
use Arokettu\ArithmeticParser\Config\UnaryPos;
use PHPUnit\Framework\TestCase;

class UnaryOperatorsTest extends TestCase
{
    public function testPrefixOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('¿', fn ($a) => $a * 3, UnaryPos::PREFIX),
            new UnaryOperator('¡', fn ($a) => $a + 5, UnaryPos::PREFIX),
        );

        // right to left
        $calc = Calculator::evaluate('¡¿2', $config);
        self::assertEquals(2 * 3 + 5, $calc);

        $calc = Calculator::evaluate('¿¡2', $config);
        self::assertEquals((2 + 5) * 3, $calc);
    }

    public function testPostfixOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('?', fn ($a) => $a * 3, UnaryPos::POSTFIX),
            new UnaryOperator('!', fn ($a) => $a + 5, UnaryPos::POSTFIX),
        );

        // right to left
        $calc = Calculator::evaluate('2?!', $config);
        self::assertEquals(2 * 3 + 5, $calc);

        $calc = Calculator::evaluate('2!?', $config);
        self::assertEquals((2 + 5) * 3, $calc);
    }

    public function testMixedOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('?', fn ($a) => $a * 3, UnaryPos::POSTFIX),
            new UnaryOperator('¿', fn ($a) => $a + 5, UnaryPos::PREFIX),
        );

        // postfix first
        $calc = Calculator::evaluate('¿2?', $config);
        self::assertEquals(2 * 3 + 5, $calc);

        // alter with brackets
        $calc = Calculator::evaluate('(¿2)?', $config);
        self::assertEquals((2 + 5) * 3, $calc);
    }
}
