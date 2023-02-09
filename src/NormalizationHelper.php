<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

final class NormalizationHelper
{
    public static function normalizeName(string $name): string
    {
        return strtoupper($name);
    }
}
