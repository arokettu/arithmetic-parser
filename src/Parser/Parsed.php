<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Parser;

use Arokettu\ArithmeticParser\Operation;

final class Parsed
{
    /**
     * @param array<int, Operation\Operation> $operations List of operations for the stack machine in the required order
     * @param array<string, Operation\Variable> $variables List of required variables: normalized name => Op object
     * @param array<string, Operation\FunctionCall> $functions List of required functions: normalized name => Op object
     */
    public function __construct(
        public readonly array $operations,
        public readonly array $variables,
        public readonly array $functions,
    ) {}

    public function asString(): string
    {
        return implode(' ', array_map(fn (Operation\Operation $o) => $o->asString(), $this->operations));
    }
}
