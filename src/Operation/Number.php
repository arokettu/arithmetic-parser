<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

final class Number implements Operation
{
    public function __construct(
        public readonly float $value,
    ) {}

    public function asString(): string
    {
        return \strval($this->value);
    }
}
