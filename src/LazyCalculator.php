<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use RuntimeException;
use SplStack;

final class LazyCalculator
{
    use BaseCalculator;

    /**
     * @throws Exceptions\CalcCallException
     * @throws Exceptions\CalcConfigException
     */
    public function calc(float ...$vars): float
    {
        $normalizedVars = $this->normalizeVars($vars);

        $stack = new SplStack();

        foreach ($this->operations as $operation) {
            switch (true) {
                case $operation instanceof Operation\Number:
                    $stack->push(new Argument\ValueArgument($operation->value));
                    break;
                case $operation instanceof Operation\Variable:
                    $this->treeifyVariable($operation, $stack, $normalizedVars);
                    break;
                case $operation instanceof Operation\FunctionCall:
                    $this->treeifyFunction($operation, $stack);
                    break;
                case $operation instanceof Operation\BinaryOperator:
                    $this->treeifyBinaryOperator($operation, $stack);
                    break;
                case $operation instanceof Operation\UnaryOperator:
                    $this->treeifyUnaryOperator($operation, $stack);
                    break;
                default:
                    throw new Exceptions\CalcConfigException('Invalid operation: ' . get_debug_type($operation));
            }
        }

        if (\count($stack) !== 1) {
            throw new Exceptions\CalcConfigException('Operation sequence is invalid');
        }

        return $stack->pop()->getValue();
    }

    private function treeifyVariable(Operation\Variable $operation, SplStack $stack, array $normalizedVars): void
    {
        $stack->push(new class ($operation, $normalizedVars) implements Argument\LazyArgument {
            public function __construct(
                private readonly Operation\Variable $var,
                private readonly array $normalizedVars,
            ) {
            }

            public function getValue(): float
            {
                if (!isset($this->normalizedVars[$this->var->normalizedName])) {
                    throw new Exceptions\UndefinedVariableException("Variable {$this->var->name} is not defined");
                }

                return $this->normalizedVars[$this->var->normalizedName];
            }
        });
    }

    private function treeifyFunction(Operation\FunctionCall $operation, SplStack $stack): void
    {
        if ($operation->arity < 0) {
            throw new Exceptions\CalcConfigException(
                "Invalid function arity, likely parser failure: {$operation->name}",
            );
        }

        try {
            $args = [];
            for ($i = 0; $i < $operation->arity; $i++) {
                $args[] = $stack->pop();
            }
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException("Not enough arguments for function call: {$operation->name}");
        }

        $stack->push(new class ($this->config, $operation, $args) implements Argument\LazyArgument {
            public function __construct(
                private readonly Config $config,
                private readonly Operation\FunctionCall $operation,
                private readonly array $args,
            ) {
            }

            public function getValue(): float
            {
                $func =
                    $this->config->getFunctions()[$this->operation->normalizedName] ??
                    throw new Exceptions\UndefinedFunctionException("Undefined function: {$this->operation->name}");
                $callValues = array_reverse($func->lazy ?
                    $this->args :
                    array_map(static fn (Argument\LazyArgument $v) => $v->getValue(), $this->args));
                return ($func->callable)(...$callValues);
            }
        });
    }

    private function treeifyBinaryOperator(Operation\BinaryOperator $operation, SplStack $stack): void
    {
        try {
            $value2 = $stack->pop();
            $value1 = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException(
                "Not enough arguments for binary operator: {$operation->operator}",
            );
        }

        switch ($operation->operator) {
            case '+':
                $stack->push(new Argument\BinaryOpArgument(static fn ($a, $b) => $a + $b, $value1, $value2, false));
                break;
            case '-':
                $stack->push(new Argument\BinaryOpArgument(static fn ($a, $b) => $a - $b, $value1, $value2, false));
                break;
            default:
                $operator = $this->config->getOperators()[$operation->operator] ?? null;
                if ($operator instanceof Config\BinaryOperator) {
                    $stack->push(new Argument\BinaryOpArgument($operator->callable, $value1, $value2, $operator->lazy));
                    break;
                }
                throw new Exceptions\CalcConfigException("Undefined binary operator: {$operation->operator}");
        }
    }

    private function treeifyUnaryOperator(Operation\UnaryOperator $operation, SplStack $stack): void
    {
        try {
            $arg = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException("Not enough arguments for unary operator: {$operation->operator}");
        }

        switch ($operation->operator) {
            case '+':
                $stack->push($arg); // noop
                break;
            case '-':
                $stack->push(new Argument\UnaryOpArgument(static fn ($v) => -$v, $arg, false));
                break;
            default:
                $operator = $this->config->getOperators()[$operation->operator] ?? null;
                if ($operator instanceof Config\UnaryOperator) {
                    $stack->push(new Argument\UnaryOpArgument($operator->callable, $arg, $operator->lazy));
                    break;
                }
                throw new Exceptions\CalcConfigException("Undefined unary operator: {$operation->operator}");
        }
    }
}
