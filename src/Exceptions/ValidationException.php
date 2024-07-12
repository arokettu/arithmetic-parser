<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Exceptions;

use DomainException;

abstract class ValidationException extends DomainException implements BaseException
{
}
