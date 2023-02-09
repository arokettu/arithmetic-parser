<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

enum OperationType
{
    case FUNCTION;
    case UNARY_OPERATOR;
    case BINARY_OPERATOR;
    case NUMBER;
    case VARIABLE;
    case BRACKET; // only on parsing
}
