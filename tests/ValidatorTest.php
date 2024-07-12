<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\MissingFunctionArgumentsException;
use Arokettu\ArithmeticParser\Exceptions\MissingFunctionsException;
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

    public function testMissingFunctions(): void
    {
        $parsed = (new Parser())->parse('a(b(), c()) + @D(1,2,3)');
        $config = Config::default();
        $config->addFunctions(
            a: fn ($a, $b) => $a + $b,
            c: fn () => 123,
        );

        $this->expectException(MissingFunctionsException::class);
        $this->expectExceptionMessage('Missing functions: b(0), @D(3)');

        Validator::assertValid($parsed, $config, []);
    }

    public function testInsufficientArguments(): void
    {
        $parsed = (new Parser())->parse('a(1,2) + b(1,2)');
        $config = Config::default();
        $config->addFunctions(
            a: fn ($a, $b) => $a + $b,
            b: fn ($a, $b, $c) => $a + $b + $c,
        );

        $this->expectException(MissingFunctionArgumentsException::class);
        $this->expectExceptionMessage('Insufficient arguments for function b(): 3 expected but only 2 provided');

        Validator::assertValid($parsed, $config, []);
    }
}
