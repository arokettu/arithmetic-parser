<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

final class Func
{
    public function __construct(
        public readonly string $name,
        public readonly \Closure $callable,
    ) {}
}
