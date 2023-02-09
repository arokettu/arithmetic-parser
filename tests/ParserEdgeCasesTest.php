<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

class ParserEdgeCasesTest extends TestCase
{
    public function testEmptyBrackets(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty brackets');

        (new Parser())->parse('1 + ()');
    }

    public function testEmptyFuncBrackets(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Empty brackets');

        (new Parser())->parse('1 + abs()');
    }

    public function testExtraOpenBracket(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Probably invalid operator combination');

        (new Parser())->parse('1 + abs(');
    }

    public function testExtraCloseBracket(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unbalanced brackets');

        (new Parser())->parse('1 + abs(123))');
    }

    public function testInvalidUnary(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Binary operator missing its first argument');

        (new Parser())->parse('* 1');
    }
}
