<?php

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
