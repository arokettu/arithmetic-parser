<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\OperatorHelper;

final class UnaryOperator implements Operator
{
    public function __construct(
        public readonly string $symbol,
        public readonly \Closure $callable,
        public readonly UnaryPos $position = UnaryPos::POSTFIX,
    ) {
        OperatorHelper::assertSymbol($symbol);
    }
}
