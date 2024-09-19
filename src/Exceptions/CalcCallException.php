<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Exceptions;

use RuntimeException;

abstract class CalcCallException extends RuntimeException implements BaseRuntimeException, CalculatorException
{
}
