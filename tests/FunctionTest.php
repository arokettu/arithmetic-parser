<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use Arokettu\ArithmeticParser\LazyCalculator;
use Arokettu\ArithmeticParser\Operation\FunctionCall;
use Arokettu\ArithmeticParser\Parser;
use DomainException;
use PHPUnit\Framework\TestCase;

class FunctionTest extends TestCase
{
    public function testFunction(): void
    {
        self::assertEquals(2, Calculator::evaluate('abs(1 - 3)'));
        self::assertEquals(2, Calculator::evaluate('abs(3 - 1)'));

        self::assertEquals(2, LazyCalculator::evaluate('abs(1 - 3)'));
        self::assertEquals(2, LazyCalculator::evaluate('abs(3 - 1)'));

        // prefix
        self::assertEquals(2, Calculator::evaluate('@abs(3 - 1)'));

        self::assertEquals(2, LazyCalculator::evaluate('@abs(3 - 1)'));
    }

    public function testMissingFunc(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: MyFunc');

        Calculator::evaluate('MyFunc(1) + 3');
    }

    public function testMissingFuncLazy(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Undefined function: MyFunc');

        LazyCalculator::evaluate('MyFunc(1) + 3');
    }

    public function testMissingFuncHiddenByLazy(): void
    {
        self::assertEquals(1, LazyCalculator::evaluate('1 or MyFunc(1) + 3'));
    }

    public function testConfigCustomFunctionByCallable(): void
    {
        $config = Config::default()->addFunctionsFromCallables(
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

    public function testConfigInvalidFuncName(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid variable or function name: 1ABC');

        $config = Config::default();
        $func = [
            '1abc' => fn () => null,
        ];
        $config->addFunctionsFromCallables(...$func);
    }

    public function testFindAllFunctions(): void
    {
        $parsed = (new Parser(Config::default()))->parse('a(B(), @c(d)) + e / f(x) - f');

        // d and e are not functions
        $funcs = array_keys($parsed->functions);
        sort($funcs);
        self::assertEquals(['A', 'B', 'C', 'F'], $funcs);
    }

    public function testCorrectArity(): void
    {
        $parsed = (new Parser(Config::default()))->parse(
            'a(1,2,3) + @a(1,2) + A(1,2,3,4) + b() / b(1) + c + c(123)'
        );

        $funcs = [
            'A' => new FunctionCall('@a', 2), // name will be taken from the lowest arity call
            'B' => new FunctionCall('b', 0),
            'C' => new FunctionCall('c', 1),
        ];

        self::assertEquals($funcs, $parsed->functions);
    }
}
