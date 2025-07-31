<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\ParseException;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserEdgeCasesArityTest extends TestCase
{
    public function testCommaTopLevel(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Param separator not inside brackets at position 1');

        (new Parser())->parse('1, 2');
    }

    public function testCommaNotFunctionCall(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Param separator outside of function call at position 9');

        (new Parser())->parse('1 + (1, 2)');
    }

    public function testCommaNotFunctionCallInsideFunctionCall(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Param separator outside of function call at position 11');

        (new Parser())->parse('1 + a((1, 2))');
    }

    public function testTwoCommas(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty expression before param separator at position 8');

        (new Parser())->parse('1 + a(1,, 2)');
    }

    public function testCommaAfterBracket(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty expression before param separator at position 6');

        (new Parser())->parse('1 + a(, 2)');
    }

    public function testCommaBeforeBracket(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Empty expression before closing bracket at position 7');

        (new Parser())->parse('1 + a(1,)');
    }


    public function testCommaBeforeBinaryOperator(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing first argument at position 5');

        (new Parser())->parse('a(1, *2, 3)');
    }

    public function testCommaAfterBinaryOperator(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (*) missing second argument at position 6');

        (new Parser())->parse('a(1, 2*, 3)');
    }

    public function testCommaAfterUnaryOperator(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\UnaryOperator(
            '!',
            static fn ($a) => $a + 1,
            Config\UnaryPos::PREFIX,
        ));

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unary prefix operator (!) missing its argument at position 5');

        (new Parser($config))->parse('a(1, !, 3)');
    }

    public function testCommaBeforeUnaryOperator(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\UnaryOperator(
            '!',
            static fn ($a) => $a + 1,
            Config\UnaryPos::POSTFIX,
        ));

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Unary postfix operator (!) missing its argument at position 5');

        (new Parser($config))->parse('a(1, !, 3)');
    }
}
