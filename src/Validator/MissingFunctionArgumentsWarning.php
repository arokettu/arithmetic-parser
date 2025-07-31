<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Config\Func;
use Arokettu\ArithmeticParser\Exceptions\MissingFunctionArgumentsException;
use Arokettu\ArithmeticParser\Exceptions\ValidationException;
use Arokettu\ArithmeticParser\Operation\FunctionCall;

final class MissingFunctionArgumentsWarning implements Warning
{
    public function __construct(
        public readonly Func $declaration,
        public readonly FunctionCall $operation,
    ) {
    }

    public function getMessage(): string
    {
        return \sprintf(
            'Insufficient arguments for function %s(): %d expected but only %d provided',
            $this->operation->name,
            $this->declaration->minArity,
            $this->operation->arity,
        );
    }

    public function toException(): ValidationException
    {
        return new MissingFunctionArgumentsException($this->getMessage());
    }
}
