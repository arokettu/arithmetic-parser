<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Lexer\Token;
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
            '[$@]?[_a-zA-Z][_a-zA-Z0-9]*',
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
        $operators = $this->config->getOperators();

        return match (true) {
            $value === '('
                => Lexer\Token::T_BRACKET_OPEN,
            $value === ')'
                => Lexer\Token::T_BRACKET_CLOSE,
            $value === '+', $value === '-'
                => Lexer\Token::T_BINARY_OPERATOR,
            isset($operators[$value]) => match (true) {
                $operators[$value] instanceof Config\BinaryOperator => Lexer\Token::T_BINARY_OPERATOR,
                $operators[$value] instanceof Config\UnaryOperator => Lexer\Token::T_UNARY_OPERATOR,
                default => Lexer\Token::T_UNRECOGNIZED,
            },
            is_numeric($value) => Lexer\Token::T_NUMBER,
            preg_match('/\\$?[_a-zA-Z][_a-zA-Z0-9]*/', $value) > 0 => Lexer\Token::T_NAME,
            default => Lexer\Token::T_UNRECOGNIZED,
        };
    }
}
