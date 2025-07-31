<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use Arokettu\ArithmeticParser\Operation\Bracket;
use Arokettu\ArithmeticParser\Operation\ParamSeparator;
use Arokettu\ArithmeticParser\Parser;
use PHPUnit\Framework\TestCase;

final class StringifyTest extends TestCase
{
    public function testString(): void
    {
        $config = Config::default();
        $config->addOperator(new Config\UnaryOperator('!', static fn ($a) => $a + 1));

        self::assertEquals(
            '1 b(0) + 2 - 3 4 ! 5 c(3) 6 + v -(1) a(3)',
            (new Parser($config))->parse('a(1 + b() - 2, c(3, 4!, 5) + 6, -v)')->asString(),
        );
    }

    public function testParseOnlyOperators(): void
    {
        self::assertEquals('(should not be in the stack)', (new ParamSeparator())->asString());
        self::assertEquals('(should not be in the stack)', (new Bracket())->asString());
    }
}
