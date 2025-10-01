<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\OperatorHelper;
use Closure;

final class UnaryOperator implements Operator
{
    public function __construct(
        public readonly string $symbol,
        public readonly Closure $callable,
        public readonly UnaryPos $position = UnaryPos::POSTFIX,
        public readonly bool $lazy = false,
    ) {
        OperatorHelper::assertSymbol($symbol);
    }
}
