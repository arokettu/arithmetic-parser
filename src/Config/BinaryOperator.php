<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\OperatorHelper;

final class BinaryOperator implements Operator
{
    public const PRIORITY_ADD = 100;
    public const PRIORITY_MUL = 200;
    public const PRIORITY_POW = 300;

    public function __construct(
        public readonly string $symbol,
        public readonly \Closure $callable,
        public readonly int $priority,
        public readonly Association $association = Association::LEFT,
    ) {
        OperatorHelper::assertSymbol($symbol);
    }
}
