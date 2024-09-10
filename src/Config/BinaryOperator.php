<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\OperatorHelper;

final class BinaryOperator implements Operator
{
    /** @deprecated BinaryPriority::OR */
    public const PRIORITY_OR = BinaryPriority::OR;
    /** @deprecated BinaryPriority::AND */
    public const PRIORITY_AND = BinaryPriority::AND;
    /** @deprecated BinaryPriority::COMPARE */
    public const PRIORITY_COMPARE = BinaryPriority::COMPARE;
    /** @deprecated BinaryPriority::ADD */
    public const PRIORITY_ADD = BinaryPriority::ADD;
    /** @deprecated BinaryPriority::MUL */
    public const PRIORITY_MUL = BinaryPriority::MUL;
    /** @deprecated BinaryPriority::POW */
    public const PRIORITY_POW = BinaryPriority::POW;

    public function __construct(
        public readonly string $symbol,
        public readonly \Closure $callable,
        public readonly int $priority,
        public readonly BinaryAssoc $association = BinaryAssoc::LEFT,
    ) {
        OperatorHelper::assertSymbol($symbol);
    }
}
