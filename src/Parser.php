<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use LogicException;
use RuntimeException;
use SplStack;

final class Parser
{
    private Config $config;

    public function __construct(Config|null $config = null)
    {
        $this->config = $config ? clone $config : Config::default();
    }

    /**
     * @throws Exceptions\ParseException
     */
    public function parse(string $input): Parser\Parsed
    {
        // note: there are a couple of GOTOs that may make it hard to track

        $lexer = new Lexer($this->config);
        $lexer->setInput($input);
        $lexer->moveNext();

        /** @var SplStack<Operation\Operation> $stack */
        $stack = new SplStack();
        /** @var array<string, Operation\FunctionCall> $funcs */
        $funcs = [];
        /** @var array<string, Operation\Variable> $vars */
        $vars = [];
        $operations = [];

        $operators = $this->config->getOperators();

        while ($lexer->lookahead) {
            $prevToken = $lexer->token;

            $lexer->moveNext();

            // detect missing / implicit operator
            if (
                // current token is a beginning of a value
                $lexer->token->isA(
                    Lexer\Token::T_NUMBER,
                    Lexer\Token::T_NAME,
                    Lexer\Token::T_BRACKET_OPEN,
                    Lexer\Token::T_UNARY_PREFIX_OPERATOR,
                ) &&
                // prev token is an end of a value
                $prevToken !== null &&
                $prevToken->isA(
                    Lexer\Token::T_NUMBER,
                    Lexer\Token::T_NAME,
                    Lexer\Token::T_BRACKET_CLOSE,
                    Lexer\Token::T_UNARY_POSTFIX_OPERATOR,
                )
            ) {
                $isFuncCall =
                    $prevToken->type === Lexer\Token::T_NAME &&
                    $lexer->token->type === Lexer\Token::T_BRACKET_OPEN;

                if (!$isFuncCall) {
                    // implicit operator is not yet supported so just throw
                    throw Exceptions\ParseException::fromToken(
                        'Missing operator',
                        $lexer->token,
                    );
                }
            }

            switch ($lexer->token->type) {
                // error
                case Lexer\Token::T_UNRECOGNIZED:
                    throw Exceptions\ParseException::fromToken(
                        \sprintf('Unexpected "%s"', $lexer->token->value),
                        $lexer->token,
                    );

                // numbers
                case Lexer\Token::T_NUMBER:
                    $operations[] = new Operation\Number(\floatval($lexer->token->value));
                    break;

                // variables and functions
                case Lexer\Token::T_NAME:
                    if ($lexer->lookahead?->type === Lexer\Token::T_BRACKET_OPEN) {
                        // function call
                        $stack->push(new Operation\FunctionCall($lexer->token->value, -1));
                        // $funcs will be stored only when actual arity is determined
                    } else {
                        // variable name
                        $operations[] = ($var = new Operation\Variable($lexer->token->value));
                        $vars[$var->normalizedName] = $var;
                    }
                    break;

                case Lexer\Token::T_BRACKET_OPEN:
                    if (
                        $lexer->lookahead?->type === Lexer\Token::T_BRACKET_CLOSE &&
                        $prevToken?->type !== Lexer\Token::T_NAME // function call with 0 params is acceptable
                    ) {
                        throw Exceptions\ParseException::fromToken('Empty brackets', $lexer->token);
                    }
                    $stack->push(new Operation\Bracket());
                    break;

                case Lexer\Token::T_PARAM_SEPARATOR:
                    if (
                        $prevToken === null ||
                        $prevToken->isA(Lexer\Token::T_BRACKET_OPEN, Lexer\Token::T_PARAM_SEPARATOR)
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            'Empty expression before param separator',
                            $lexer->token,
                        );
                    }

                    if ($lexer->lookahead?->type === Lexer\Token::T_BRACKET_CLOSE) {
                        throw Exceptions\ParseException::fromToken(
                            'Empty expression before closing bracket',
                            $lexer->token,
                        );
                    }

                    if ($stack->isEmpty()) {
                        throw Exceptions\ParseException::fromToken(
                            'Param separator not inside brackets',
                            $lexer->token,
                        );
                    }
                    // push everything to the next separator or bracket
                    $operation = $stack->top();
                    while (
                        !($operation instanceof Operation\Bracket) &&
                        !($operation instanceof Operation\ParamSeparator)
                    ) {
                        $operations[] = $stack->pop();
                        $operation = $stack->top();
                    }

                    $stack->push(new Operation\ParamSeparator());
                    break; // param separator

                case Lexer\Token::T_BRACKET_CLOSE:
                    if ($prevToken?->type === Lexer\Token::T_BRACKET_OPEN) {
                        $arity = 0;
                        $stack->pop(); // ignore the bracket
                        goto handleFunc; // this can only happen during function call
                    }

                    try {
                        $operation = $stack->pop();
                        $separators = 0;

                        while (!($operation instanceof Operation\Bracket)) {
                            if ($operation instanceof Operation\ParamSeparator) {
                                $separators += 1;
                            } else {
                                $operations[] = $operation;
                            }
                            // bracket will be removed from the stack and not added to $operations
                            $operation = $stack->pop();
                        }
                    } catch (RuntimeException) {
                        throw Exceptions\ParseException::fromToken('Unmatched closing bracket', $lexer->token);
                    }

                    // brackets that are not function calls can't contain separators
                    if ($stack->isEmpty() || !($stack->top() instanceof Operation\FunctionCall)) {
                        if ($separators !== 0) {
                            throw Exceptions\ParseException::fromToken(
                                'Param separator outside of function call',
                                $lexer->token,
                            );
                        }
                        break;
                    }

                    // handle function
                    $arity = $separators + 1;

                    handleFunc:
                    $operation = $stack->pop();
                    if (!($operation instanceof Operation\FunctionCall)) {
                        throw new LogicException('Parser entered an invalid state'); // @codeCoverageIgnore
                    }

                    $operations[] = $func = new Operation\FunctionCall($operation->name, $arity); // write correct arity
                    // update $funcs, store minimal actually called arity
                    if (!isset($funcs[$func->normalizedName]) || $funcs[$func->normalizedName]->arity > $func->arity) {
                        $funcs[$func->normalizedName] = $func;
                    }
                    break; // bracket close

                case Lexer\Token::T_UNARY_PREFIX_OPERATOR:
                    unaryPrefix: // jump from unary +/- detection (T_BINARY_OPERATOR)
                    if (
                        $lexer->lookahead === null ||
                        $lexer->lookahead->type === Lexer\Token::T_BRACKET_CLOSE ||
                        $lexer->lookahead->type === Lexer\Token::T_BINARY_OPERATOR && (
                            $lexer->lookahead->value !== '+' && $lexer->lookahead->value !== '-' // unary prefix
                        ) ||
                        $lexer->lookahead->type === Lexer\Token::T_UNARY_POSTFIX_OPERATOR ||
                        $lexer->lookahead->type === Lexer\Token::T_PARAM_SEPARATOR
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            "Unary prefix operator ({$lexer->token->value}) missing its argument",
                            $lexer->token,
                        );
                    }
                    $stack->push(new Operation\UnaryOperator($lexer->token->value));

                    break;

                case Lexer\Token::T_UNARY_POSTFIX_OPERATOR:
                    if (
                        $prevToken === null ||
                        $prevToken->type === Lexer\Token::T_BRACKET_OPEN ||
                        $prevToken->type === Lexer\Token::T_BINARY_OPERATOR ||
                        $prevToken->type === Lexer\Token::T_UNARY_PREFIX_OPERATOR ||
                        $prevToken->type === Lexer\Token::T_PARAM_SEPARATOR
                    ) {
                        throw Exceptions\ParseException::fromToken(
                            "Unary postfix operator ({$lexer->token->value}) missing its argument",
                            $lexer->token,
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
                                $lexer->token,
                            );
                    }

                    if ($operator instanceof Config\BinaryOperator || $operator === null) {
                        // check if binary operator has its first argument
                        // also handle unary - and + here
                        if (
                            $prevToken === null ||
                            $prevToken->type === Lexer\Token::T_BRACKET_OPEN ||
                            $prevToken->type === Lexer\Token::T_BINARY_OPERATOR ||
                            $prevToken->type === Lexer\Token::T_UNARY_PREFIX_OPERATOR ||
                            $prevToken->type === Lexer\Token::T_PARAM_SEPARATOR
                        ) {
                            if ($lexer->token->value === '+' || $lexer->token->value === '-') {
                                goto unaryPrefix;
                            }

                            throw Exceptions\ParseException::fromToken(
                                "Binary operator ({$lexer->token->value}) missing first argument",
                                $lexer->token,
                            );
                        }

                        // check if binary operator has its second argument
                        if (
                            $lexer->lookahead === null ||
                            $lexer->lookahead->type === Lexer\Token::T_BRACKET_CLOSE ||
                            $lexer->lookahead->type === Lexer\Token::T_BINARY_OPERATOR && (
                                $lexer->lookahead->value !== '+' && $lexer->lookahead->value !== '-' // unary prefix
                            ) ||
                            $lexer->lookahead->type === Lexer\Token::T_UNARY_POSTFIX_OPERATOR ||
                            $lexer->lookahead->type === Lexer\Token::T_PARAM_SEPARATOR
                        ) {
                            throw Exceptions\ParseException::fromToken(
                                "Binary operator ({$lexer->token->value}) missing second argument",
                                $lexer->token,
                            );
                        }

                        // regular operators
                        // missing operator is + or -
                        $priority = $operator?->priority ?? Config\BinaryPriority::ADD;
                        $association = $operator?->association ?? Config\BinaryAssoc::LEFT;

                        while (\count($stack) > 0) {
                            $stackTop = $stack->top();
                            switch (true) {
                                case $stackTop instanceof Operation\FunctionCall:
                                    throw new LogicException('Parser entered an invalid state'); // @codeCoverageIgnore
                                case $stackTop instanceof Operation\UnaryOperator:
                                    $operations[] = $stack->pop();
                                    continue 2; // continue while
                                case $stackTop instanceof Operation\BinaryOperator:
                                    $stackTopOperator = $operators[$stackTop->operator] ?? null;
                                    $stackTopPriority = $stackTopOperator?->priority ??
                                        Config\BinaryPriority::ADD; // missing operator is + or -
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

                    throw new LogicException('Unknown binary operator: ' . $operator::class); // @codeCoverageIgnore

                default:
                    // @codeCoverageIgnoreStart
                    throw new LogicException('Unexpected token: ' . $lexer->token->type->name);
                    // @codeCoverageIgnoreEnd
            }
        }

        // add remaining operators
        foreach ($stack as $op) {
            if (
                !($op instanceof Operation\BinaryOperator) &&
                !($op instanceof Operation\UnaryOperator)
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
