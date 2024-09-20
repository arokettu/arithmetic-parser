<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Exceptions\UndefinedVariableException;
use Arokettu\ArithmeticParser\LazyCalculator;
use PHPUnit\Framework\TestCase;

class DefaultFunctionsTest extends TestCase
{
    public function testDefined(): void
    {
        self::assertEquals(1, LazyCalculator::evaluate('defined(x)', x: 123));
        self::assertEquals(0, LazyCalculator::evaluate('defined(x)'));

        self::assertEquals(1, Calculator::evaluate('defined(x)', x: 123));
    }

    public function testDefinedNonLazy(): void
    {
        $this->expectException(UndefinedVariableException::class);
        $this->expectExceptionMessage('Variable x is not defined');

        self::assertEquals(1, Calculator::evaluate('defined(x)'));
    }
}
