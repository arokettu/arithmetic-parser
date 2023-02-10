<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class Variable implements Operation
{
    public readonly string $name;
    public readonly string $normalizedName;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->normalizedName = NameHelper::normalizeVar($name);
    }
}
