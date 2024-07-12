<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Exceptions\ValidationException;

abstract class AbstractWarning
{
    abstract public function getMessage(): string;
    abstract public function toException(): ValidationException;
}
