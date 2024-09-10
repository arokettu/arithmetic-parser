<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Argument;

use Closure;

/**
 * @internal
 */
final class BinaryOpArgument implements LazyArgument
{
    public function __construct(
        private readonly Closure $callable,
        private readonly LazyArgument $a,
        private readonly LazyArgument $b,
        private readonly bool $lazy,
    ) {
    }

    public function getValue(): float
    {
        $a = $this->lazy ? $this->a : $this->a->getValue();
        $b = $this->lazy ? $this->b : $this->b->getValue();
        return ($this->callable)($a, $b);
    }
}
