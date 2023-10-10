<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

final class OperatorHelper
{
    public static function assertSymbol(string $symbol): void
    {
        if ($symbol === '') {
            throw new \InvalidArgumentException('Operator symbol must not be an empty string');
        }

        if ($symbol === '+' || $symbol === '-') {
            throw new \InvalidArgumentException('+ and - are reserved and cannot be configured');
        }

        if (preg_match('/[.\d\s]/', $symbol)) {
            throw new \InvalidArgumentException('Operator symbol must not contain digits, dots, and spaces');
        }
    }
}
