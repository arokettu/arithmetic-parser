<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Config;

final class BinaryPriority
{
    public const OR = 1000;
    public const AND = 2000;
    public const COMPARE = 3000;
    public const ADD = 4000;
    public const MUL = 5000;
    public const POW = 6000;
}
