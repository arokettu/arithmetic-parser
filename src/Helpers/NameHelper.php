<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

/**
 * @internal
 */
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
        return strtoupper($name);
    }
}
