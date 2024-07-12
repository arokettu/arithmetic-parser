<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Exceptions\ValidationException;

interface Warning
{
    public function getMessage(): string;
    public function toException(): ValidationException;
}
