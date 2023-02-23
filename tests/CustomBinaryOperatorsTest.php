<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use PHPUnit\Framework\TestCase;

class CustomBinaryOperatorsTest extends TestCase
{
    public function testLeftAssociative(): void
    {
        $config = Config::default()->addOperator(
            new Config\BinaryOperator('^', pow(...), Config\BinaryOperator::PRIORITY_POW, Config\BinaryAssoc::LEFT)
        );

        $eval = Calculator::evaluate('2 ^ 3 ^ 4', $config);

        self::assertEquals((2 ** 3) ** 4, $eval);
    }

    public function testRightAssociative(): void
    {
        $config = Config::default()->addOperator(
            new Config\BinaryOperator('^', pow(...), Config\BinaryOperator::PRIORITY_POW, Config\BinaryAssoc::RIGHT)
        );

        $eval = Calculator::evaluate('2 ^ 3 ^ 4', $config);

        self::assertEquals(2 ** 3 ** 4, $eval);
    }

    public function testMulticharOperators(): void
    {
        $config = Config::default()->addOperator(
            new Config\BinaryOperator('**', pow(...), Config\BinaryOperator::PRIORITY_POW, Config\BinaryAssoc::RIGHT)
        );

        // ** should take precedence over *
        $eval = Calculator::evaluate('2 * 3 ** 4', $config);

        self::assertEquals(2 * 3 ** 4, $eval);
    }

    public function testAlphaOperators(): void
    {
        $config = Config::default()->setOperators(
            new Config\BinaryOperator('add', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD),
            new Config\BinaryOperator('mul', fn ($a, $b) => $a * $b, Config\BinaryOperator::PRIORITY_MUL),
        );

        // ** should take precedence over *
        $eval = Calculator::evaluate('2 add 2 mul 2', $config);

        self::assertEquals(2 + 2 * 2, $eval);
    }
}
