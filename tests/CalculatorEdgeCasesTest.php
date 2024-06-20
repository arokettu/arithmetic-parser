<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use Arokettu\ArithmeticParser\Operation\BinaryOperator;
use Arokettu\ArithmeticParser\Operation\Bracket;
use Arokettu\ArithmeticParser\Operation\FunctionCall;
use Arokettu\ArithmeticParser\Operation\Number;
use Arokettu\ArithmeticParser\Operation\UnaryOperator;
use PHPUnit\Framework\TestCase;

class CalculatorEdgeCasesTest extends TestCase
{
    public function testWrongLength(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new Number(3),
            new BinaryOperator('+'),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Operation sequence is invalid');
        (new Calculator($operations))->calc();
    }

    public function testWrongOperator(): void
    {
        $operations = [
            new Bracket(),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Invalid operation: Arokettu\ArithmeticParser\Operation\Bracket');
        (new Calculator($operations))->calc();
    }

    public function testWrongArity(): void
    {
        $operations = [
            new FunctionCall('test', -1),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Invalid function arity, likely parser failure: test');
        (new Calculator($operations))->calc();
    }

    public function testWrongBinaryOperator(): void
    {
        $operations = [
            new Number(1),
            new Number(2),
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined binary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testWrongUnaryOperator(): void
    {
        $operations = [
            new Number(1),
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined unary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForUnary(): void
    {
        $operations = [
            new UnaryOperator('x'),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Not enough arguments for unary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForBinary(): void
    {
        $operations = [
            new BinaryOperator('x'),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Not enough arguments for binary operator: x');
        (new Calculator($operations))->calc();
    }

    public function testNotEnoughArgsForFunc(): void
    {
        $operations = [
            new FunctionCall('x', 1),
        ];

        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Not enough arguments for function call: x');
        (new Calculator($operations))->calc();
    }
}
