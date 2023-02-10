<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

/**
 * @internal
 */
final class NormalizationHelper
{
    public static function normalizeName(string $name): string
    {
        return strtoupper($name);
    }
}
