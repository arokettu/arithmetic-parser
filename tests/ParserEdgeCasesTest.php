<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Config\UnaryOperator;
use Arokettu\ArithmeticParser\Config\UnaryPos;
use Arokettu\ArithmeticParser\Exceptions\ParseException;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserEdgeCasesTest extends TestCase
{
    public function testEmptyBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty brackets at position 4');

        (new Parser())->parse('1 + ()');
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
        $this->expectExceptionMessage('Binary operator (*) missing second argument at position 2');

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

    public function testWrongOrderPostfixUnary(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unary postfix operator (~) missing its argument at position 0');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::POSTFIX),
        );

        (new Parser($config))->parse('~5');
    }

    public function testWrongOrderPrefixUnary(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 1');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('5~');
    }

    public function testWrongOrderPrefixUnaryInBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 6');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('(1 * 5~)');
    }

    public function testWrongOrderUnaryPlus(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (+) missing second argument at position 1');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('5+');
    }

    public function testWrongOrderUnaryPlusInBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (+) missing second argument at position 6');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('(1 * 5+)');
    }

    public function testWrongOrderOnlyUnaryPlusInBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unary prefix operator (+) missing its argument at position 5');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('5 / (+)')->asString();
    }

    public function testWrongOrderUnaryPlusInFuncCall(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (+) missing second argument at position 5');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('abc(5+, 1)');
    }

    public function testWrongOrderOnlyUnaryPlusInFuncCall(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unary prefix operator (+) missing its argument at position 4');

        $config = Config::default()->addOperators(
            new UnaryOperator('~', static fn ($a) => $a, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('abc(+, 5)')->asString();
    }

    public function testNoOperator(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 1');

        (new Parser())->parse('5x');
    }

    public function testNoOperatorBetweenNumbers(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 2');

        (new Parser())->parse('5 10');
    }

    public function testNoOperatorBetweenVariables(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 2');

        (new Parser())->parse('$x$y');
    }

    public function testNoOperatorBetweenBrackets(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 6');

        (new Parser())->parse('(1+2) (3+4)');
    }

    public function testNoOperatorBetweenPrefixAndPostfix(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Missing operator at position 5');

        $config = Config::default()->addOperators(
            new UnaryOperator('?', static fn ($a) => $a * 2, UnaryPos::POSTFIX),
            new UnaryOperator('¿', static fn ($a) => $a + 2, UnaryPos::PREFIX),
        );

        (new Parser($config))->parse('¿2? ¿3?');
    }
}
