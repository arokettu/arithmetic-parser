<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class Func
{
    public readonly string $normalizedName;
    public readonly \Closure $callable;

    public function __construct(
        public readonly string $name,
        callable $callable,
    ) {
        $this->normalizedName = NameHelper::normalizeFunc($name);
        NameHelper::assertName($this->normalizedName);
        $this->callable = $callable(...);
    }
}
