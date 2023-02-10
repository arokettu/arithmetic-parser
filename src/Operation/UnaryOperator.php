<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

final class UnaryOperator implements Operation
{
    public function __construct(
        public readonly string $operator,
    ) {}
}
