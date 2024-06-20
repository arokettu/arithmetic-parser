<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\OperatorHelper;

final class BinaryOperator implements Operator
{
    public const PRIORITY_OR = 1000;
    public const PRIORITY_AND = 2000;
    public const PRIORITY_COMPARE = 3000;
    public const PRIORITY_ADD = 4000;
    public const PRIORITY_MUL = 5000;
    public const PRIORITY_POW = 6000;

    public function __construct(
        public readonly string $symbol,
        public readonly \Closure $callable,
        public readonly int $priority,
        public readonly BinaryAssoc $association = BinaryAssoc::LEFT,
    ) {
        OperatorHelper::assertSymbol($symbol);
    }
}
