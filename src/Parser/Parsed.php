<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Parser;

use Arokettu\ArithmeticParser\Operation;

final class Parsed
{
    /**
     * @param array<int, Operation\Operation> $operations
     * @param array<string, Operation\Variable> $variables List of normalized function names
     * @param array<string, Operation\FunctionCall> $functions List of normalized variable names
     */
    public function __construct(
        public readonly array $operations,
        public readonly array $variables,
        public readonly array $functions,
    ) {}
}
