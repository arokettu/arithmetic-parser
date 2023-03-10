<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class Func
{
    public readonly string $name;
    public readonly string $normalizedName;
    public readonly \Closure $callable;

    public function __construct(string $name, callable $callable)
    {
        $this->name = $name;
        $this->normalizedName = NameHelper::normalizeFunc($name);
        $this->callable = $callable(...);
    }
}
