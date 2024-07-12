<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Exceptions\MissingFunctionsException;
use Arokettu\ArithmeticParser\Exceptions\ValidationException;
use Arokettu\ArithmeticParser\Operation\FunctionCall;

final class MissingFunctionsWarning implements Warning
{
    /**
     * @param array<string, FunctionCall> $missingFunctions
     */
    public function __construct(
        public readonly array $missingFunctions,
    ) {
    }

    public function getMessage(): string
    {
        return 'Missing functions: ' . implode(', ', array_map(
            fn (FunctionCall $op) => $op->asString(),
            $this->missingFunctions
        ));
    }

    public function toException(): ValidationException
    {
        return new MissingFunctionsException($this->getMessage());
    }
}
