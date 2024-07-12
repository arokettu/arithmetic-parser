<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Exceptions\MissingVariablesException;
use Arokettu\ArithmeticParser\Exceptions\ValidationException;
use Arokettu\ArithmeticParser\Operation\Variable;

final class MissingVariablesWarning implements Warning
{
    /**
     * @param array<string, Variable> $missingVariables
     */
    public function __construct(
        public readonly array $missingVariables,
    ) {
    }

    public function getMessage(): string
    {
        return 'Missing variables: ' . implode(', ', array_map(
            fn (Variable $op) => $op->asString(),
            $this->missingVariables
        ));
    }

    public function toException(): ValidationException
    {
        return new MissingVariablesException($this->getMessage());
    }
}
