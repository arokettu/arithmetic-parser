<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

// phpcs:disable SlevomatCodingStandard.ControlStructures.AssignmentInCondition.AssignmentInCondition

use Arokettu\ArithmeticParser\Argument\LazyArgument;
use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use Arokettu\ArithmeticParser\Exceptions\CalcConfigException;
use Arokettu\ArithmeticParser\Exceptions\UndefinedVariableException;
use Arokettu\ArithmeticParser\LazyCalculator;
use Arokettu\ArithmeticParser\Operation\BinaryOperator;
use Arokettu\ArithmeticParser\Operation\Bracket;
use Arokettu\ArithmeticParser\Operation\FunctionCall;
use Arokettu\ArithmeticParser\Operation\Number;
use Arokettu\ArithmeticParser\Operation\UnaryOperator;
use PHPUnit\Framework\TestCase;

final class CalculatorEdgeCasesTest extends TestCase
{
    public function testWrongLength(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new Number(3),
            new BinaryOperator('+'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Operation sequence is invalid');
        (new Calculator($operations))->calc();
    }

    public function testWrongLengthLazy(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new Number(3),
            new BinaryOperator('+'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Operation sequence is invalid');
        (new LazyCalculator($operations))->calc();
    }

    public function testWrongOperator(): void
    {
        $operations = [
            new Bracket(),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Invalid operation: Arokettu\ArithmeticParser\Operation\Bracket');
        (new Calculator($operations))->calc();
    }

    public function testWrongOperatorLazy(): void
    {
        $operations = [
            new Bracket(),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Invalid operation: Arokettu\ArithmeticParser\Operation\Bracket');
        (new LazyCalculator($operations))->calc();
    }


    public function testWrongArity(): void
    {
        $operations = [
            new FunctionCall('test', -1),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Invalid function arity, likely parser failure: test');
        (new Calculator($operations))->calc();
    }

    public function testWrongArityLazy(): void
    {
        $operations = [
            new FunctionCall('test', -1),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Invalid function arity, likely parser failure: test');
        (new LazyCalculator($operations))->calc();
    }

    public function testWrongBinaryOperator(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Undefined binary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testWrongBinaryOperatorLazy(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Undefined binary operator: x');
        (new LazyCalculator($operations))->calc();
    }

    public function testWrongUnaryOperator(): void
    {
        $operations = [
            new Number(1),
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Undefined unary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testWrongUnaryOperatorLazy(): void
    {
        $operations = [
            new Number(1),
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Undefined unary operator: x');
        (new LazyCalculator($operations))->calc();
    }

    public function testNotEnoughArgsForUnary(): void
    {
        $operations = [
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for unary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForUnaryLazy(): void
    {
        $operations = [
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for unary operator: x');
        (new LazyCalculator($operations))->calc();
    }

    public function testNotEnoughArgsForBinary(): void
    {
        $operations = [
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for binary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForBinaryLazy(): void
    {
        $operations = [
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for binary operator: x');
        (new LazyCalculator($operations))->calc();
    }

    public function testNotEnoughArgsForFunc(): void
    {
        $operations = [
            new FunctionCall('x', 1),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for function call: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForFuncLazy(): void
    {
        $operations = [
            new FunctionCall('x', 1),
        ];

        $this->expectException(CalcConfigException::class);
        $this->expectExceptionMessage('Not enough arguments for function call: x');
        (new LazyCalculator($operations))->calc();
    }

    public function testLazyFuncHandled(): void
    {
        self::assertEquals(2, Calculator::evaluate('if (a, b, c)', a: 1, b: 2, c: 3));
        self::assertEquals(3, Calculator::evaluate('if (a, b, c)', a: 0, b: 2, c: 3));

        self::assertEquals(2, LazyCalculator::evaluate('if (a, b, c)', a: 1, b: 2, c: 3));
        self::assertEquals(3, LazyCalculator::evaluate('if (a, b, c)', a: 0, b: 2, c: 3));

        // lazy when lazy
        self::assertEquals(2, LazyCalculator::evaluate('if (a, b, c)', a: 1, b: 2));
    }

    public function testLazyFuncNotActuallyLazy(): void
    {
        $this->expectException(UndefinedVariableException::class);
        $this->expectExceptionMessage('Variable c is not defined');

        Calculator::evaluate('if (a, b, c)', a: 1, b: 2);
    }

    public function testLazyUnaryOperatorHandled(): void
    {
        // make an operator like x? === if(defined(x), x, 0)
        $config = Config::default();
        $config->addOperator(new Config\UnaryOperator(
            '?',
            static function (LazyArgument $a) {
                try {
                    return $a->getValue();
                } catch (UndefinedVariableException) {
                    return 0;
                }
            },
            lazy: true,
        ));

        self::assertEquals(1, Calculator::evaluate('a?', $config, a: 1));
        self::assertEquals(1, LazyCalculator::evaluate('a?', $config, a: 1));

        // lazy when lazy
        self::assertEquals(0, LazyCalculator::evaluate('a?', $config));
    }

    public function testLazyUnaryOperatorNotActuallyLazy(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\UnaryOperator(
            '?',
            static function (LazyArgument $a) {
                try {
                    return $a->getValue();
                } catch (CalcCallException) {
                    return 0;
                }
            },
            lazy: true,
        ));

        $this->expectException(UndefinedVariableException::class);
        $this->expectExceptionMessage('Variable a is not defined');

        Calculator::evaluate('a?', $config);
    }

    public function testLazyBinaryOperatorHandled(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\BinaryOperator(
            '||',
            static function (LazyArgument $a, LazyArgument $b) {
                if (($v = $a->getValue())) {
                    return $v;
                }
                return $b->getValue();
            },
            priority: Config\BinaryPriority::OR,
            lazy: true,
        ));

        self::assertEquals(2, Calculator::evaluate('2 || a', $config, a: 3));
        self::assertEquals(3, Calculator::evaluate('0 || a', $config, a: 3));

        self::assertEquals(2, LazyCalculator::evaluate('2 || a', $config, a: 3));
        self::assertEquals(3, LazyCalculator::evaluate('0 || a', $config, a: 3));

        // lazy when lazy
        self::assertEquals(2, LazyCalculator::evaluate('2 || a', $config));
    }

    public function testLazyBinaryOperatorNotActuallyLazy(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\BinaryOperator(
            '||',
            static function (LazyArgument $a, LazyArgument $b) {
                if (($v = $a->getValue())) {
                    return $v;
                }
                return $b->getValue();
            },
            priority: Config\BinaryPriority::OR,
            lazy: true,
        ));

        $this->expectException(UndefinedVariableException::class);
        $this->expectExceptionMessage('Variable a is not defined');

        Calculator::evaluate('2 || a', $config);
    }
}
