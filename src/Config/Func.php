<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\FuncHelper;
use Arokettu\ArithmeticParser\Helpers\NameHelper;
use Closure;

final class Func
{
    public readonly string $normalizedName;
    public readonly Closure $callable;
    public readonly int $minArity;

    public function __construct(
        public readonly string $name,
        callable $callable,
        public readonly bool $lazy = false,
    ) {
        $this->normalizedName = NameHelper::normalizeFunc($name);
        NameHelper::assertName($this->normalizedName);
        $this->callable = $callable(...);
        $this->minArity = FuncHelper::arity($this->callable);
    }
}
