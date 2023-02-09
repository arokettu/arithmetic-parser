<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Doctrine\Common\Lexer\AbstractLexer;

final class Lexer extends AbstractLexer
{
    protected function getCatchablePatterns(): array
    {
        return [
            '[_a-zA-Z][_a-zA-Z0-9]*',
            '\d+(?:\.\d+)?',
            '[\-+*\/]',
            '\(',
            '\)',
        ];
    }

    protected function getNonCatchablePatterns(): array
    {
        return [
            '\s+',
        ];
    }

    protected function getType(string &$value): Token
    {
        switch (true) {
            // brackets
            case $value === '(':
                return Token::T_BRACKET_OPEN;
            case $value === ')':
                return Token::T_BRACKET_CLOSE;
            case $value === '+':
            case $value === '-':
            case $value === '*':
            case $value === '/':
                return Token::T_OPERATOR;
            case is_numeric($value):
                return Token::T_NUMBER;
            case preg_match('/[_a-zA-Z][_a-zA-Z0-9]*/', $value) > 0:
                return Token::T_NAME;
            default:
                throw new \RuntimeException('Invalid token: ' . $value);
        }
    }
}
