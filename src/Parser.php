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

        $operators = $this->config->getOperators();

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

                case Lexer\Token::T_UNARY_PREFIX_OPERATOR:
                    if (
                        $lexer->lookahead === null ||
                        $lexer->lookahead->type === Lexer\Token::T_BRACKET_CLOSE ||
                        $lexer->lookahead->type === Lexer\Token::T_BINARY_OPERATOR && (
                            $lexer->lookahead->value !== '+' && $lexer->lookahead->value !== '-' // unary prefix
                        ) ||
                        $lexer->lookahead->type === Lexer\Token::T_UNARY_POSTFIX_OPERATOR
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            "Unary prefix operator ({$lexer->token->value}) missing its argument",
                            $lexer->token
                        );
                    }
                    $stack->push(new Operation\UnaryOperator($lexer->token->value));

                    break;

                case Lexer\Token::T_UNARY_POSTFIX_OPERATOR:
                    if (
                        $prevToken === null ||
                        $prevToken->type === Lexer\Token::T_BRACKET_OPEN ||
                        $prevToken->type === Lexer\Token::T_BINARY_OPERATOR ||
                        $prevToken->type === Lexer\Token::T_UNARY_PREFIX_OPERATOR
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            "Unary postfix operator ({$lexer->token->value}) missing its argument",
                            $lexer->token
                        );
                    }
                    $operations[] = new Operation\UnaryOperator($lexer->token->value);

                    break;

                case Lexer\Token::T_BINARY_OPERATOR:
                    if ($lexer->token->value === '+' || $lexer->token->value === '-') {
                        $operator = null; // special handling
                    } else {
                        $operator = $operators[$lexer->token->value] ??
                            throw Exceptions\ParseException::fromToken(
                                'Unknown operator ' . $lexer->token->value,
                                $lexer->token
                            );
                    }

                    if ($operator instanceof Config\BinaryOperator || $operator === null) {
                        // check if binary operator has its first argument
                        // also handle unary - and + here
                        if (
                            $prevToken === null ||
                            $prevToken->type === Lexer\Token::T_BRACKET_OPEN ||
                            $prevToken->type === Lexer\Token::T_BINARY_OPERATOR ||
                            $prevToken->type === Lexer\Token::T_UNARY_PREFIX_OPERATOR
                        ) {
                            if ($lexer->token->value === '+' || $lexer->token->value === '-') {
                                $stack->push(new Operation\UnaryOperator($lexer->token->value));
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
                            $lexer->lookahead->type === Lexer\Token::T_BRACKET_CLOSE ||
                            $lexer->lookahead->type === Lexer\Token::T_BINARY_OPERATOR && (
                                $lexer->lookahead->value !== '+' && $lexer->lookahead->value !== '-' // unary prefix
                            ) ||
                            $lexer->lookahead->type === Lexer\Token::T_UNARY_POSTFIX_OPERATOR
                        ) {
                            throw Exceptions\ParseException::fromToken(
                                "Binary operator ({$lexer->token->value}) missing second argument",
                                $lexer->token
                            );
                        }

                        // regular operators
                        $priority = $operator?->priority ?? Config\BinaryOperator::PRIORITY_ADD;
                        $association = $operator?->association ?? Config\BinaryAssoc::LEFT;

                        while (\count($stack) > 0) {
                            $stackTop = $stack->peek();
                            switch (true) {
                                case $stackTop instanceof Operation\FunctionCall:
                                case $stackTop instanceof Operation\UnaryOperator:
                                    $operations[] = $stack->pop();
                                    continue 2; // continue while
                                case $stackTop instanceof Operation\BinaryOperator:
                                    $stackTopOperator = $operators[$stackTop->operator] ?? null;
                                    $stackTopPriority = $stackTopOperator?->priority ??
                                        Config\BinaryOperator::PRIORITY_ADD;
                                    if (
                                        $stackTopPriority > $priority ||
                                        ($stackTopPriority === $priority && $association === Config\BinaryAssoc::LEFT)
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

                    throw Exceptions\ParseException::fromToken('Unknown binary operator', $lexer->token);

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
}
