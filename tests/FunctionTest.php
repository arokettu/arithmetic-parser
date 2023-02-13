<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    public function testFunction(): void
    {
        self::assertEquals(2, Calculator::evaluate('abs(1 - 3)'));
        self::assertEquals(2, Calculator::evaluate('abs(3 - 1)'));
    }

    public function testMissingFunc(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: MyFunc');

        Calculator::evaluate('MyFunc(1) + 3');
    }
}
