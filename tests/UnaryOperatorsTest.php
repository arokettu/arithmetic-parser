<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Config\UnaryOperator;
use Arokettu\ArithmeticParser\Config\UnaryPos;
use Arokettu\ArithmeticParser\LazyCalculator;
use PHPUnit\Framework\TestCase;

final class UnaryOperatorsTest extends TestCase
{
    public function testPrefixOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('¿', static fn ($a) => $a * 3, UnaryPos::PREFIX),
            new UnaryOperator('¡', static fn ($a) => $a + 5, UnaryPos::PREFIX),
        );

        // right to left
        $calc = Calculator::evaluate('¡¿2', $config);
        $lazy = LazyCalculator::evaluate('¡¿2', $config);
        self::assertEquals(2 * 3 + 5, $calc);
        self::assertEquals(2 * 3 + 5, $lazy);

        $calc = Calculator::evaluate('¿¡2', $config);
        $lazy = LazyCalculator::evaluate('¿¡2', $config);
        self::assertEquals((2 + 5) * 3, $calc);
        self::assertEquals((2 + 5) * 3, $lazy);
    }

    public function testPostfixOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('?', static fn ($a) => $a * 3, UnaryPos::POSTFIX),
            new UnaryOperator('!', static fn ($a) => $a + 5, UnaryPos::POSTFIX),
        );

        // right to left
        $calc = Calculator::evaluate('2?!', $config);
        $lazy = LazyCalculator::evaluate('2?!', $config);
        self::assertEquals(2 * 3 + 5, $calc);
        self::assertEquals(2 * 3 + 5, $lazy);

        $calc = Calculator::evaluate('2!?', $config);
        $lazy = LazyCalculator::evaluate('2!?', $config);
        self::assertEquals((2 + 5) * 3, $calc);
        self::assertEquals((2 + 5) * 3, $lazy);
    }

    public function testMixedOrder(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('?', static fn ($a) => $a * 3, UnaryPos::POSTFIX),
            new UnaryOperator('¿', static fn ($a) => $a + 5, UnaryPos::PREFIX),
        );

        // postfix first
        $calc = Calculator::evaluate('¿2?', $config);
        $lazy = LazyCalculator::evaluate('¿2?', $config);
        self::assertEquals(2 * 3 + 5, $calc);
        self::assertEquals(2 * 3 + 5, $lazy);

        // alter with brackets
        $calc = Calculator::evaluate('(¿2)?', $config);
        $lazy = LazyCalculator::evaluate('(¿2)?', $config);
        self::assertEquals((2 + 5) * 3, $calc);
        self::assertEquals((2 + 5) * 3, $lazy);
    }
}
