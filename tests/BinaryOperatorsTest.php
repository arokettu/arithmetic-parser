<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\ParseException;
use PHPUnit\Framework\TestCase;

class BinaryOperatorsTest extends TestCase
{
    public function testOperators(): void
    {
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

    public function testConfigRemoveOperator(): void
    {
        $config = Config::default();
        self::assertArrayHasKey('/', $config->getOperators());

        $config->removeOperators('/');
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unexpected "/" at position 2');

        Calculator::evaluate('2 / 2', $config);
    }

    public function testConfigClearOperators(): void
    {
        $config = Config::default();
        self::assertArrayHasKey('*', $config->getOperators());

        $config->clearOperators();
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unexpected "*" at position 2');

        Calculator::evaluate('2 * 2', $config);
    }
}
