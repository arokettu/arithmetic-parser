<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Argument\ValueArgument;
use RuntimeException;
use SplStack;

final class Calculator
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
                    $stack->push($operation->value);
                    break;
                case $operation instanceof Operation\Variable:
                    if (!isset($normalizedVars[$operation->normalizedName])) {
                        throw new Exceptions\UndefinedVariableException("Variable {$operation->name} is not defined");
                    }
                    $stack->push($normalizedVars[$operation->normalizedName]);
                    break;
                case $operation instanceof Operation\FunctionCall:
                    $this->performFunction($operation, $stack);
                    break;
                case $operation instanceof Operation\BinaryOperator:
                    $this->performBinaryOperator($operation, $stack);
                    break;
                case $operation instanceof Operation\UnaryOperator:
                    $this->performUnaryOperator($operation, $stack);
                    break;
                default:
                    throw new Exceptions\CalcConfigException('Invalid operation: ' . get_debug_type($operation));
            }
        }

        if (\count($stack) !== 1) {
            throw new Exceptions\CalcConfigException('Operation sequence is invalid');
        }

        return $stack->pop();
    }

    private function performFunction(Operation\FunctionCall $operation, SplStack $stack): void
    {
        if ($operation->arity < 0) {
            throw new Exceptions\BadFunctionCallException(
                "Invalid function arity, likely parser failure: {$operation->name}"
            );
        }

        try {
            $values = [];
            for ($i = 0; $i < $operation->arity; $i++) {
                $values[] = $stack->pop();
            }
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException("Not enough arguments for function call: {$operation->name}");
        }

        $func =
            $this->config->getFunctions()[$operation->normalizedName] ??
            throw new Exceptions\UndefinedFunctionException("Undefined function: {$operation->name}");
        $callValues = array_reverse(
            $func->lazy ?
            array_map(fn ($v) => new Argument\ValueArgument($v), $values) :
            $values
        );
        $stack->push(($func->callable)(...$callValues));
    }

    private function performBinaryOperator(Operation\BinaryOperator $operation, SplStack $stack): void
    {
        try {
            $value2 = $stack->pop();
            $value1 = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException(
                "Not enough arguments for binary operator: {$operation->operator}"
            );
        }

        switch ($operation->operator) {
            case '+':
                $stack->push($value1 + $value2);
                break;
            case '-':
                $stack->push($value1 - $value2);
                break;
            default:
                $operator = $this->config->getOperators()[$operation->operator] ?? null;
                if ($operator instanceof Config\BinaryOperator) {
                    if ($operator->lazy) {
                        $stack->push(($operator->callable)(new ValueArgument($value1), new ValueArgument($value2)));
                    } else {
                        $stack->push(($operator->callable)($value1, $value2));
                    }
                    break;
                }
                throw new Exceptions\CalcConfigException("Undefined binary operator: {$operation->operator}");
        }
    }

    private function performUnaryOperator(Operation\UnaryOperator $operation, SplStack $stack): void
    {
        try {
            $value = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcConfigException("Not enough arguments for unary operator: {$operation->operator}");
        }

        switch ($operation->operator) {
            case '+':
                $stack->push($value);
                break;
            case '-':
                $stack->push(-$value);
                break;
            default:
                $operator = $this->config->getOperators()[$operation->operator] ?? null;
                if ($operator instanceof Config\UnaryOperator) {
                    if ($operator->lazy) {
                        $stack->push(($operator->callable)(new ValueArgument($value)));
                    } else {
                        $stack->push(($operator->callable)($value));
                    }
                    break;
                }
                throw new Exceptions\CalcConfigException("Undefined unary operator: {$operation->operator}");
        }
    }
}
