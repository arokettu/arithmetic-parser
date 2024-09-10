<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Argument;

use Closure;

/**
 * @internal
 */
final class UnaryOpArgument implements LazyArgument
{
    public function __construct(
        private readonly Closure $callable,
        private readonly LazyArgument $a,
        private readonly bool $lazy,
    ) {
    }

    public function getValue(): float
    {
        $a = $this->lazy ? $this->a : $this->a->getValue();
        return ($this->callable)($a);
    }
}
