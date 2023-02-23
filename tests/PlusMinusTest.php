<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Calculator;
use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Config\UnaryOperator;
use Arokettu\ArithmeticParser\Config\UnaryPos;
use Arokettu\ArithmeticParser\Exceptions\ParseException;
use Arokettu\ArithmeticParser\Operation\BinaryOperator as BinaryOp;
use Arokettu\ArithmeticParser\Operation\Number;
use Arokettu\ArithmeticParser\Operation\UnaryOperator as UnaryOp;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

class PlusMinusTest extends TestCase
{
    public function testOperators(): void
    {
        self::assertEquals(2 + 3, Calculator::evaluate('2 + 3'));
        self::assertEquals(2 - 3, Calculator::evaluate('2 - 3'));
    }

    public function testUnaryOperators(): void
    {
        self::assertEquals(2, Calculator::evaluate('2'));
        self::assertEquals(5, Calculator::evaluate('+(2+3)'));
        self::assertEquals(-2, Calculator::evaluate('-2'));
        self::assertEquals(-5, Calculator::evaluate('-(2+3)'));
    }

    public function testParsing(): void
    {
        $parsed = (new Parser())->parse('+-+ 1 + -+- 2')->operations;
        self::assertEquals([
            new Number(1),
            new UnaryOp('+'),
            new UnaryOp('-'),
            new UnaryOp('+'),
            new Number(2),
            new UnaryOp('-'),
            new UnaryOp('+'),
            new UnaryOp('-'),
            new BinaryOp('+'),
        ], $parsed);
    }

    public function testUnaryCombination(): void
    {
        $config = Config::default()->addOperators(
            new UnaryOperator('¿', fn ($a) => $a * 2, UnaryPos::PREFIX),
            new UnaryOperator('¡', fn ($a) => $a + 2, UnaryPos::PREFIX),
            new UnaryOperator('?', fn ($a) => $a * 2, UnaryPos::POSTFIX),
            new UnaryOperator('!', fn ($a) => $a + 2, UnaryPos::POSTFIX),
        );

        // postfix
        $val = Calculator::evaluate('-2?! + -2!?', $config);
        self::assertEquals(-(2 * 2 + 2) + -((2 + 2) * 2), $val);

        // prefix
        $val = Calculator::evaluate('¿-¡2 + ¡+¿2', $config);
        self::assertEquals(-(2 + 2) * 2 + (+(2 * 2) + 2), $val);
    }

    public function testBinaryCombination(): void
    {
        self::assertEquals(4, Calculator::evaluate('-2 * -2'));
    }

    public function testBinaryInvalidCombination(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Binary operator (-) missing second argument at position 1');

        Calculator::evaluate('2- * 2-');
    }
}
