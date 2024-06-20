<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

final class ParamSeparator implements Operation
{
    public function asString(): string
    {
        return '(should not be in the stack)';
    }
}
