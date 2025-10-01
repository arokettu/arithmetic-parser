<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Validator;

use Arokettu\ArithmeticParser\Exceptions\ValidationException;

interface Warning
{
    public function getMessage(): string;
    public function toException(): ValidationException;
}
