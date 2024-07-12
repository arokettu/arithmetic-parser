<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\MissingVariablesException;
use Arokettu\ArithmeticParser\Parser;
use Arokettu\ArithmeticParser\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        // todo
        $this->markTestSkipped();
    }

    public function testIsValid(): void
    {
        // todo
        $this->markTestSkipped();
    }

    public function testAssertValidValid(): void
    {
        // todo
        $this->markTestSkipped();
    }

    public function testAssertValidInvalid(): void
    {
        // todo
        $this->markTestSkipped();
    }

    public function testMissingVariables(): void
    {
        $parsed = (new Parser())->parse('a + b + c + $D');

        $this->expectException(MissingVariablesException::class);
        $this->expectExceptionMessage('Missing variables: b, $D');

        Validator::assertValid($parsed, Config::default(), ['a', '$c']);
    }
}
