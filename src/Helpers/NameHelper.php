<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

use DomainException;

final class NameHelper
{
    public static function normalizeVar(string $name): string
    {
        if (str_starts_with($name, '$')) {
            $name = substr($name, 1);
        }
        return strtoupper($name);
    }

    public static function normalizeFunc(string $name): string
    {
        if (str_starts_with($name, '@')) {
            $name = substr($name, 1);
        }
        return strtoupper($name);
    }

    public static function assertName(string $normalizedName): void
    {
        if (!preg_match('/^[_A-Z][_A-Z0-9]*$/', $normalizedName)) {
            throw new DomainException('Invalid variable or function name: ' . $normalizedName);
        }
    }
}
