<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
use Arokettu\ArithmeticParser\LazyCalculator;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{
    public function testVariable(): void
    {
        // var names are case-insensitive
        self::assertEquals(5, Calculator::evaluate('MyVar1 + 3', myVar1: 2));
        // $ prefix is optional
        self::assertEquals(5, Calculator::evaluate('MyVar1 + 3', ...['$myVar1' => 2]));
        self::assertEquals(5, Calculator::evaluate('$MyVar1 + 3', ...['$myVar1' => 2]));
        self::assertEquals(5, Calculator::evaluate('$MyVar1 + 3', myVar1: 2));

        // lazy

        // var names are case-insensitive
        self::assertEquals(5, LazyCalculator::evaluate('MyVar1 + 3', myVar1: 2));
        // $ prefix is optional
        self::assertEquals(5, LazyCalculator::evaluate('MyVar1 + 3', ...['$myVar1' => 2]));
        self::assertEquals(5, LazyCalculator::evaluate('$MyVar1 + 3', ...['$myVar1' => 2]));
        self::assertEquals(5, LazyCalculator::evaluate('$MyVar1 + 3', myVar1: 2));
    }

    public function testDuplicateVariable(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Duplicate variable name: MYVAR1');
        // var names are case-insensitive
        Calculator::evaluate('MyVar1 + 3', myVar1: 2, MYVAR1: 3);
    }

    public function testDuplicateVariableLazy(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Duplicate variable name: MYVAR1');
        // var names are case-insensitive
        LazyCalculator::evaluate('MyVar1 + 3', myVar1: 2, MYVAR1: 3);
    }

    public function testNoPositionalParams(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Invalid variable name: 0');

        Calculator::evaluate('2 + 3', Config::default(), 2, 3);
    }

    public function testNoPositionalParamsLazy(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Invalid variable name: 0');

        LazyCalculator::evaluate('2 + 3', Config::default(), 2, 3);
    }

    public function testMissingVar(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Variable MyVar1 is not defined');

        Calculator::evaluate('MyVar1 + 3');
    }

    public function testMissingVarLazy(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Variable MyVar1 is not defined');

        LazyCalculator::evaluate('MyVar1 + 3');
    }

    public function testMissingVarHiddenByLazy(): void
    {
        self::assertEquals(1, LazyCalculator::evaluate('true() or MyVar1 + 3'));
    }

    public function testMissingVarHiddenByLazyInNonLazyStillThrows(): void
    {
        $this->expectException(CalcCallException::class);
        $this->expectExceptionMessage('Variable MyVar1 is not defined');

        Calculator::evaluate('true() or MyVar1 + 3');
    }

    public function testFindAllVariables(): void
    {
        $parsed = (new Parser(Config::default()))->parse('a + b(c) * d - $e / F');

        // b is not a variable
        self::assertEquals(['A', 'C', 'D', 'E', 'F'], array_keys($parsed->variables));
    }
}
