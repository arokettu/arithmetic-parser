<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

use DomainException;

final class OperatorHelper
{
    public static function assertSymbol(string $symbol): void
    {
        if ($symbol === '') {
            throw new DomainException('Operator symbol must not be an empty string');
        }

        if ($symbol === '+' || $symbol === '-') {
            throw new DomainException('+ and - are reserved and cannot be configured');
        }

        if ($symbol === '(' || $symbol === ')') {
            throw new DomainException('Brackets ( and ) are reserved and cannot be configured');
        }

        if ($symbol === ',') {
            throw new DomainException('Comma (,) is reserved and cannot be configured');
        }

        if (preg_match('/[.\d\s]/', $symbol)) {
            throw new DomainException('Operator symbol must not contain digits, dots, and spaces');
        }
    }
}
