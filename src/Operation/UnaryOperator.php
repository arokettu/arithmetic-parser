<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

final class UnaryOperator implements Operation
{
    public function __construct(
        public readonly string $operator,
    ) {}

    public function asString(): string
    {
        if ($this->operator === '-' || $this->operator === '+') {
            return $this->operator . '(1)';
        } else {
            return $this->operator;
        }
    }
}
