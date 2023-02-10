<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

final class BinaryOperator implements Operation
{
    public function __construct(
        public readonly string $operator,
    ) {}
}
