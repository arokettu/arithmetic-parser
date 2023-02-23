<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * @extends AbstractLexer<Lexer\Token, string>
 */
final class Lexer extends AbstractLexer
{
    private Config $config;
    private readonly array $operators;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ? clone $config : Config::default();

        $operators = array_keys($this->config->getOperators());
        usort($operators, fn (string $a, string $b): int => \strlen($b) <=> \strlen($a));
        $this->operators = $operators;
    }

    protected function getCatchablePatterns(): array
    {
        $operators = '(?:' . implode(')|(?:', array_map(
            fn (string $s): string => preg_quote($s, '/'),
            $this->operators,
        )) . ')';

        return [
            '\d+(?:\.\d+)?',
            '[\-+]',
            $operators,
            '\(',
            '\)',
            '\$?[_a-zA-Z][_a-zA-Z0-9]*',
        ];
    }

    protected function getNonCatchablePatterns(): array
    {
        return [
            '\s+',
        ];
    }

    /**
     * mixed is for compatibility with lexer 2.x, actually it's string only
     */
    protected function getType(mixed &$value): Lexer\Token
    {
        switch (true) {
            // brackets
            case $value === '(':
                return Lexer\Token::T_BRACKET_OPEN;
            case $value === ')':
                return Lexer\Token::T_BRACKET_CLOSE;
            case $value === '+':
            case $value === '-':
            case \in_array($value, $this->operators):
                return Lexer\Token::T_OPERATOR;
            case is_numeric($value):
                return Lexer\Token::T_NUMBER;
            case preg_match('/\\$?[_a-zA-Z][_a-zA-Z0-9]*/', $value) > 0:
                return Lexer\Token::T_NAME;
            default:
                return Lexer\Token::T_UNRECOGNIZED;
        }
    }
}
