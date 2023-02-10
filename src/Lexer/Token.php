<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Lexer;

enum Token
{
    case T_NAME;
    case T_OPERATOR;
    case T_NUMBER;
    case T_BRACKET_OPEN;
    case T_BRACKET_CLOSE;
}
