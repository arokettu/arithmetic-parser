<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Exceptions;

use UnexpectedValueException;

abstract class ValidationException extends UnexpectedValueException implements BaseException
{
}
