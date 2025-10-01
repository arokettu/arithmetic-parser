<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Exceptions;

use RuntimeException;

abstract class CalcCallException extends RuntimeException implements BaseRuntimeException, CalculatorException
{
}
