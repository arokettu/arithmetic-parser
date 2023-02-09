<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

final class Operation
{
    public function __construct(
        public readonly OperationType $type,
        public readonly mixed $value = null,
    ) {}
}
