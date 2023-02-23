<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Parser
{
    private Config $config;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ? clone $config : Config::default();
    }

    /**
     * @throws Exceptions\ParseException
     */
    public function parse(string $input): Parser\Parsed
    {
        $lexer = new Lexer($this->config);
        $lexer->setInput($input);
        $lexer->moveNext();

        /** @var Stack<Operation\Operation> $stack */
        $stack = new Stack();
        $funcs = [];
        $vars = [];
        $operations = [];

        while ($lexer->lookahead) {
            $prevToken = $lexer->token;

            $lexer->moveNext();

            switch ($lexer->token->type) {
                // error
                case Lexer\Token::T_UNRECOGNIZED:
                    throw Exceptions\ParseException::fromToken(
                        sprintf('Unexpected "%s"', $lexer->token->value),
                        $lexer->token
                    );

                // numbers
                case Lexer\Token::T_NUMBER:
                    $operations[] = new Operation\Number(\floatval($lexer->token->value));
                    break;

                // variables and functions
                case Lexer\Token::T_NAME:
                    if ($lexer->lookahead?->type === Lexer\Token::T_BRACKET_OPEN) {
                        // function call
                        $stack->push($func = new Operation\FunctionCall($lexer->token->value));
                        $funcs[$func->normalizedName] = $func;
                    } else {
                        // variable name
                        $operations[] = ($var = new Operation\Variable($lexer->token->value));
                        $vars[$var->normalizedName] = $var;
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
                    if ($lexer->token->value === '+' || $lexer->token->value === '-') {
                        $operator = null; // special handling
                    } else {
                        $operator = $this->config->getOperators()[$lexer->token->value] ??
                            throw Exceptions\ParseException::fromToken(
                                'Unknown operator ' . $lexer->token->value,
                                $lexer->token
                            );
                    }

                    if ($operator instanceof Config\UnaryOperator) {
                        // todo: handle postfix operators
                        // todo: handle prefix operators
                        throw new \LogicException('TODO');
                    }

                    if ($operator instanceof Config\BinaryOperator || $operator === null) {
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
                        $priority = $operator?->priority ?? Config\BinaryOperator::PRIORITY_ADD;
                        $association = $operator?->association ?? Config\Association::LEFT;

                        while (\count($stack) > 0) {
                            $stackTop = $stack->peek();
                            switch (true) {
                                case $stackTop instanceof Operation\FunctionCall:
                                    $operations[] = $stack->pop();
                                    continue 2; // continue while
                                case $stackTop instanceof Operation\BinaryOperator:
                                    $stackTopOperator = $this->config->getOperators()[$stackTop->operator] ?? null;
                                    $stackTopPriority = $stackTopOperator?->priority ??
                                        Config\BinaryOperator::PRIORITY_ADD;
                                    if (
                                        $stackTopPriority > $priority ||
                                        ($stackTopPriority === $priority && $association === Config\Association::LEFT)
                                    ) {
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
                    } // binary operator

                default:
                    throw Exceptions\ParseException::fromToken('Unexpected token', $lexer->token);
            }
        }

        // add remaining operators
        foreach ($stack as $op) {
            if (
                !($op instanceof Operation\BinaryOperator) &&
                !($op instanceof Operation\UnaryOperator) &&
                !($op instanceof Operation\FunctionCall)
            ) {
                throw new Exceptions\ParseException('Probably invalid operator combination');
            }
            $operations[] = $op;
        }

        return new Parser\Parsed(
            operations: $operations,
            variables: $vars,
            functions: $funcs,
        );
    }

    private function getPriority(string $symbol): int
    {
        if ($symbol === '+' || $symbol === '-') {
            return Config\BinaryOperator::PRIORITY_ADD;
        }

        $operator = $this->config->getOperators()[$symbol] ??
            throw new Exceptions\ParseException('Misconfigured binary operator: ' . $symbol);

        if ($operator instanceof Config\BinaryOperator) {
            return $operator->priority;
        }

        throw new Exceptions\ParseException("Operator {$symbol} is not binary");
    }
}
