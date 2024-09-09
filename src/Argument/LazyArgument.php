<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Argument;

interface LazyArgument
{
    public function getValue(): float;
}
