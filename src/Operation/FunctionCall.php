<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Operation;

use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class FunctionCall implements Operation
{
    public readonly string $normalizedName;

    public function __construct(
        public readonly string $name,
        public readonly int $arity,
    ) {
        $this->normalizedName = NameHelper::normalizeFunc($name);
        NameHelper::assertName($this->normalizedName);
    }

    public function asString(): string
    {
        return "{$this->name}({$this->arity})";
    }
}
