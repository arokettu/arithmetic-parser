<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Parser
{
    /**
     * @return array<int, Parser\Operation>
     */
    public function parse(string $input): array
    {
        $lexer = new Lexer();
        $lexer->setInput($input);
        $lexer->moveNext();

        /** @var Stack<Parser\Operation> $stack */
        $stack = new Stack();
        $operations = [];

        while ($lexer->lookahead) {
            $prevToken = $lexer->token;

            $lexer->moveNext();

            switch ($lexer->token->type) {
                // numbers
                case Token::T_NUMBER:
                    $operations[] = new Parser\Operation(Parser\OperationType::NUMBER, value: $lexer->token->value);
                    break;

                // todo: handle postfix operators

                // todo: handle prefix operators

                // variables and functions
                case Token::T_NAME:
                    $value = [
                        'name' => $lexer->token->value,
                        'normalized' => Helpers\NormalizationHelper::normalizeName($lexer->token->value),
                    ];

                    if ($lexer->lookahead?->type === Token::T_BRACKET_OPEN) {
                        // function call
                        $stack->push(new Parser\Operation(Parser\OperationType::FUNCTION, value: $value));
                    } else {
                        // variable name
                        $operations[] = new Parser\Operation(Parser\OperationType::VARIABLE, value: $value);
                    }
                    break;

                case Token::T_BRACKET_OPEN:
                    if ($lexer->lookahead?->type === Token::T_BRACKET_CLOSE) {
                        throw new \RuntimeException('Empty brackets');
                    }
                    $stack->push(new Parser\Operation(Parser\OperationType::BRACKET));
                    break;

                case Token::T_BRACKET_CLOSE:
                    try {
                        $operation = $stack->pop();

                        while ($operation->type !== Parser\OperationType::BRACKET) {
                            $operations[] = $operation;
                            // bracket will be removed from the stack and not added to $operations
                            $operation = $stack->pop();
                        }
                    } catch (\UnderflowException) {
                        throw new \RuntimeException('Unbalanced brackets');
                    }
                    break;

                case Token::T_OPERATOR:
                    // unary - and +
                    if (
                        $prevToken === null ||
                        $prevToken->type === Token::T_BRACKET_OPEN ||
                        $prevToken->type === Token::T_OPERATOR
                    ) {
                        if ($lexer->token->value === '+') {
                            break; // unary plus is a noop, drop it
                        }
                        if ($lexer->token->value === '-') {
                            // if the next value is a constant, change it
                            if ($lexer->lookahead->type === Token::T_NUMBER) {
                                $lexer->lookahead->value = '-' . $lexer->lookahead->value;
                            } else {
                                $stack->push(new Parser\Operation(Parser\OperationType::UNARY_OPERATOR, value: '-'));
                            }
                            break;
                        }

                        throw new \RuntimeException('Binary operator missing its first argument');
                    }

                    // regular operators
                    $priority = $this->getPriority($lexer->token->value);

                    while (\count($stack) > 0) {
                        $stackTop = $stack->peek();
                        switch ($stackTop->type) {
                            case Parser\OperationType::FUNCTION:
                                $operations[] = $stack->pop();
                                continue 2; // continue while
                            case Parser\OperationType::BINARY_OPERATOR:
                                $stackTopPriority = $this->getPriority($stackTop->value);
                                if ($stackTopPriority >= $priority) {
                                    $operations[] = $stack->pop();
                                    continue 2; // continue while
                                }
                                break 2; // break while
                            default:
                                break 2; // break while
                        }
                    }

                    $stack->push(new Parser\Operation(Parser\OperationType::BINARY_OPERATOR, $lexer->token->value));

                    break;

                default:
                    throw new \RuntimeException('Unexpected token');
            }
        }

        // add remaining operators
        foreach ($stack as $op) {
            if (
                $op->type !== Parser\OperationType::BINARY_OPERATOR &&
                $op->type !== Parser\OperationType::UNARY_OPERATOR &&
                $op->type !== Parser\OperationType::FUNCTION
            ) {
                throw new \RuntimeException('Probably invalid operator combination');
            }
            $operations[] = $op;
        }

        return [...$operations];
    }

    private function getPriority(string $operator): int
    {
        return match ($operator) {
            '+', '-' => 1,
            '*', '/' => 2,
        };
    }
}
