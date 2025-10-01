<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Helpers;

use Closure;
use ReflectionFunction;

final class FuncHelper
{
    public static function arity(Closure $closure): int
    {
        $refl = new ReflectionFunction($closure);
        return $refl->getNumberOfRequiredParameters();
    }
}
