<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Argument;

/**
 * @internal
 */
final class ValueArgument implements LazyArgument
{
    public function __construct(
        public readonly float $value,
    ) {
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
