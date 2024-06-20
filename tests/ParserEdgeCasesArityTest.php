<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Exceptions\ParseException;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

class ParserEdgeCasesArityTest extends TestCase
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
}
