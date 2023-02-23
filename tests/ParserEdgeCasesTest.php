<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Exceptions\ParseException;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

class ParserEdgeCasesTest extends TestCase
{
    public function testEmptyBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty brackets at position 4');

        (new Parser())->parse('1 + ()');
    }

    public function testEmptyFuncBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty brackets at position 7');

        (new Parser())->parse('1 + abs()');
    }

    public function testExtraOpenBracket(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Probably invalid operator combination');

        (new Parser())->parse('1 + abs(');
    }

    public function testExtraCloseBracket(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unmatched closing bracket at position 12');

        (new Parser())->parse('1 + abs(123))');
    }

    public function testInvalidUnary(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing first argument at position 0');

        (new Parser())->parse('* 1');
    }

    public function testInvalidUnaryInBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing first argument at position 5');

        (new Parser())->parse('5 + (* 1)');
    }

    public function testInvalidUnaryAfterBinary(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing first argument at position 4');

        (new Parser())->parse('5 * * 5');
    }

    public function testInvalidPostfixUnary(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing second argument at position 2');

        (new Parser())->parse('5 *');
    }

    public function testInvalidPostfixUnaryInBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing second argument at position 3');

        (new Parser())->parse('(5 *) + 1');
    }

    public function testInvalidToken(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unexpected "%" at position 2');

        (new Parser())->parse('1 % 2');
    }
}
