<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Lexer;

enum Token
{
    case T_NAME;
    case T_UNARY_PREFIX_OPERATOR;
    case T_UNARY_POSTFIX_OPERATOR;
    case T_BINARY_OPERATOR;
    case T_NUMBER;
    case T_BRACKET_OPEN;
    case T_BRACKET_CLOSE;
    case T_PARAM_SEPARATOR;
    case T_UNRECOGNIZED;
}
