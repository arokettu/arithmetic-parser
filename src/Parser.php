<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Parser
{
    /**
     * @return array<int, Operation\Operation>
     * @throws Exceptions\ParseException
     */
    public function parse(string $input): array
    {
        $lexer = new Lexer();
        $lexer->setInput($input);
        $lexer->moveNext();

        /** @var Stack<Operation\Operation> $stack */
        $stack = new Stack();
        $operations = [];

        while ($lexer->lookahead) {
            $prevToken = $lexer->token;

            $lexer->moveNext();

            switch ($lexer->token->type) {
                // error
                case Lexer\Token::T_UNRECOGNIZED:
                    throw Exceptions\ParseException::fromToken(sprintf('Unexpected "%s"', $lexer->token->value), $lexer->token);

                // numbers
                case Lexer\Token::T_NUMBER:
                    $operations[] = new Operation\Number(\floatval($lexer->token->value));
                    break;

                // todo: handle postfix operators

                // todo: handle prefix operators

                // variables and functions
                case Lexer\Token::T_NAME:
                    if ($lexer->lookahead?->type === Lexer\Token::T_BRACKET_OPEN) {
                        // function call
                        $stack->push(new Operation\FunctionCall($lexer->token->value));
                    } else {
                        // variable name
                        $operations[] = new Operation\Variable($lexer->token->value);
                    }
                    break;

                case Lexer\Token::T_BRACKET_OPEN:
                    if ($lexer->lookahead?->type === Lexer\Token::T_BRACKET_CLOSE) {
                        throw Exceptions\ParseException::fromToken('Empty brackets', $lexer->token);
                    }
                    $stack->push(new Operation\Bracket());
                    break;

                case Lexer\Token::T_BRACKET_CLOSE:
                    try {
                        $operation = $stack->pop();

                        while (!($operation instanceof Operation\Bracket)) {
                            $operations[] = $operation;
                            // bracket will be removed from the stack and not added to $operations
                            $operation = $stack->pop();
                        }
                    } catch (\UnderflowException) {
                        throw Exceptions\ParseException::fromToken('Unmatched closing bracket', $lexer->token);
                    }
                    break;

                case Lexer\Token::T_OPERATOR:
                    // check if binary operator has its first argument
                    // also handle unary - and + here
                    if (
                        $prevToken === null ||
                        $prevToken->type === Lexer\Token::T_BRACKET_OPEN ||
                        $prevToken->type === Lexer\Token::T_OPERATOR
                    ) {
                        if ($lexer->token->value === '+') {
                            break; // unary plus is a noop, drop it
                        }
                        if ($lexer->token->value === '-') {
                            // if the next value is a constant, change it
                            if ($lexer->lookahead->type === Lexer\Token::T_NUMBER) {
                                $lexer->moveNext();
                                $operations[] = new Operation\Number(-\floatval($lexer->token->value));
                            } else {
                                $stack->push(new Operation\UnaryOperator('-'));
                            }
                            break;
                        }

                        throw Exceptions\ParseException::fromToken(
                            "Binary operator ({$lexer->token->value}) missing first argument",
                            $lexer->token
                        );
                    }

                    // check if binary operator has its second argument
                    if (
                        $lexer->lookahead === null ||
                        $lexer->lookahead->type === Lexer\Token::T_BRACKET_CLOSE
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            "Binary operator ({$lexer->token->value}) missing second argument",
                            $lexer->token
                        );
                    }

                    // regular operators
                    $priority = $this->getPriority($lexer->token->value);

                    while (\count($stack) > 0) {
                        $stackTop = $stack->peek();
                        switch (true) {
                            case $stackTop instanceof Operation\FunctionCall:
                                $operations[] = $stack->pop();
                                continue 2; // continue while
                            case $stackTop instanceof Operation\BinaryOperator:
                                $stackTopPriority = $this->getPriority($stackTop->operator);
                                if ($stackTopPriority >= $priority) {
                                    $operations[] = $stack->pop();
                                    continue 2; // continue while
                                }
                                break 2; // break while
                            default:
                                break 2; // break while
                        }
                    }

                    $stack->push(new Operation\BinaryOperator($lexer->token->value));

                    break;

                default:
                    throw new \RuntimeException('Unexpected token');
            }
        }

        // add remaining operators
        foreach ($stack as $op) {
            if (
                !($op instanceof Operation\BinaryOperator) &&
                !($op instanceof Operation\UnaryOperator) &&
                !($op instanceof Operation\FunctionCall)
            ) {
                throw new \RuntimeException('Probably invalid operator combination');
            }
            $operations[] = $op;
        }

        return $operations;
    }

    private function getPriority(string $operator): int
    {
        return match ($operator) {
            '+', '-' => 1,
            '*', '/' => 2,
        };
    }
}
