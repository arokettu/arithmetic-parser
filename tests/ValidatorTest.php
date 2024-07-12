<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Exceptions\MissingFunctionArgumentsException;
use Arokettu\ArithmeticParser\Exceptions\MissingFunctionsException;
use Arokettu\ArithmeticParser\Exceptions\MissingVariablesException;
use Arokettu\ArithmeticParser\Operation\FunctionCall;
use Arokettu\ArithmeticParser\Operation\Variable;
use Arokettu\ArithmeticParser\Parser;
use Arokettu\ArithmeticParser\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $parsedValid = (new Parser())->parse('a + b * c + log(123) + abs(-2)');
        // try to collect all problems
        $parsedInvalid = (new Parser())->parse('a + b + c() + d(123) + if(1) + log()');
        $config = Config::default();

        $warnings = [
            new Validator\MissingVariablesWarning([
                'A' => new Variable('a'),
                'B' => new Variable('b'),
            ]),
            new Validator\MissingFunctionsWarning([
                'C' => new FunctionCall('c', 0),
                'D' => new FunctionCall('d', 1),
            ]),
            new Validator\MissingFunctionArgumentsWarning(
                $config->getFunctions()['IF'],
                new FunctionCall('if', 1),
            ),
            new Validator\MissingFunctionArgumentsWarning(
                $config->getFunctions()['LOG'],
                new FunctionCall('log', 0),
            ),
        ];

        self::assertEquals([], Validator::validate($parsedValid, $config, ['a', 'b', 'c']));
        self::assertEquals($warnings, Validator::validate($parsedInvalid, $config, []));
    }

    public function testIsValid(): void
    {
        $parsedValid = (new Parser())->parse('a + b * c + log(123) + abs(-2)');
        // try to collect all problems
        $parsedInvalid = (new Parser())->parse('a + b + c() + d(123) + if(1) + log()');
        $config = Config::default();

        self::assertTrue(Validator::isValid($parsedValid, $config, ['a', 'b', 'c']));
        self::assertFalse(Validator::isValid($parsedInvalid, $config, []));
    }

    public function testIsValidWithWarnings(): void
    {
        $parsedValid = (new Parser())->parse('a + b * c + log(123) + abs(-2)');
        // try to collect all problems
        $parsedInvalid = (new Parser())->parse('a + b + c() + d(123) + if(1) + log()');
        $config = Config::default();

        $warnings = [
            new Validator\MissingVariablesWarning([
                'A' => new Variable('a'),
                'B' => new Variable('b'),
            ]),
            new Validator\MissingFunctionsWarning([
                'C' => new FunctionCall('c', 0),
                'D' => new FunctionCall('d', 1),
            ]),
            new Validator\MissingFunctionArgumentsWarning(
                $config->getFunctions()['IF'],
                new FunctionCall('if', 1),
            ),
            new Validator\MissingFunctionArgumentsWarning(
                $config->getFunctions()['LOG'],
                new FunctionCall('log', 0),
            ),
        ];

        self::assertTrue(Validator::isValid($parsedValid, $config, ['a', 'b', 'c'], $warningsValid));
        self::assertEquals([], $warningsValid);
        self::assertFalse(Validator::isValid($parsedInvalid, $config, [], $warningsInvalid));
        self::assertEquals($warnings, $warningsInvalid);
    }

    public function testAssertValidValid(): void
    {
        $parsedValid = (new Parser())->parse('a + b * c + log(123) + abs(-2)');

        Validator::assertValid($parsedValid, Config::default(), ['a', 'b', 'c']);

        $this->expectNotToPerformAssertions(); // mark success here
    }

    public function testAssertValidInvalid(): void
    {
        // try to collect all problems
        $parsedInvalid = (new Parser())->parse('a + b + c() + d(123) + if(1) + log()');

        $this->expectException(MissingVariablesException::class); // the first warning will throw
        $this->expectExceptionMessage('Missing variables: a, b');

        Validator::assertValid($parsedInvalid, Config::default(), []);
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
