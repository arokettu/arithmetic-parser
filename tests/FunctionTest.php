<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    public function testFunction(): void
    {
        self::assertEquals(2, Calculator::evaluate('abs(1 - 3)'));
        self::assertEquals(2, Calculator::evaluate('abs(3 - 1)'));

        // prefix
        self::assertEquals(2, Calculator::evaluate('@abs(3 - 1)'));
    }

    public function testMissingFunc(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: MyFunc');

        Calculator::evaluate('MyFunc(1) + 3');
    }

    public function testConfigCustomFunctionByCallable(): void
    {
        $config = Config::default()->addFunctions(
            mul2: fn ($a) => $a * 2,
        );
        self::assertEquals(4, Calculator::evaluate('mul2(2)', $config));
    }

    public function testConfigAddFunctionByObject(): void
    {
        $config = Config::default()->addFunctions(
            new Config\Func('mul2', fn ($a) => $a * 2),
        );
        self::assertEquals(4, Calculator::evaluate('mul2(2)', $config));
    }

    public function testConfigRemoveFunction(): void
    {
        $config = Config::default();
        self::assertArrayHasKey('ABS', $config->getFunctions()); // normalized

        $config->removeFunction('abs');
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: abs');

        Calculator::evaluate('abs(1 - 3)', $config);
    }

    public function testConfigClearFunctions(): void
    {
        $config = Config::default()->clearFunctions();
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: abs');

        Calculator::evaluate('abs(1 - 3)', $config);
    }
}
