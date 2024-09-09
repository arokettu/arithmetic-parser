<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use RuntimeException;
use SplStack;

final class Calculator
{
    use BaseCalculator;

    /**
     * @throws Exceptions\CalcCallException
     */
    public function calc(float ...$vars): float
    {
        $normalizedVars = [];

        foreach ($vars as $name => $value) {
            if (!\is_string($name)) {
                throw new Exceptions\CalcCallException('Invalid variable name: ' . $name);
            }
            $normalizedName = Helpers\NameHelper::normalizeVar($name);
            if (isset($normalizedVars[$normalizedName])) {
                throw new Exceptions\CalcCallException('Duplicate variable name: ' . $name);
            }
            $normalizedVars[$normalizedName] = $value;
        }

        $stack = new SplStack();

        foreach ($this->operations as $operation) {
            switch (true) {
                case $operation instanceof Operation\Number:
                    $stack->push($operation->value);
                    break;
                case $operation instanceof Operation\Variable:
                    if (!isset($normalizedVars[$operation->normalizedName])) {
                        throw new Exceptions\CalcCallException("Variable {$operation->name} is not defined");
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
                    throw new Exceptions\CalcCallException('Invalid operation: ' . get_debug_type($operation));
            }
        }

        if (\count($stack) !== 1) {
            throw new Exceptions\CalcCallException('Operation sequence is invalid');
        }

        return $stack->pop();
    }

    private function performFunction(Operation\FunctionCall $operation, SplStack $stack): void
    {
        if ($operation->arity < 0) {
            throw new Exceptions\CalcCallException("Invalid function arity, likely parser failure: {$operation->name}");
        }

        try {
            $values = [];
            for ($i = 0; $i < $operation->arity; $i++) {
                $values[] = $stack->pop();
            }
        } catch (RuntimeException) {
            throw new Exceptions\CalcCallException("Not enough arguments for function call: {$operation->name}");
        }

        $func =
            $this->config->getFunctions()[$operation->normalizedName] ??
            throw new Exceptions\CalcCallException("Undefined function: {$operation->name}");
        $stack->push(($func->callable)(...array_reverse($values)));
    }

    private function performBinaryOperator(Operation\BinaryOperator $operation, SplStack $stack): void
    {
        try {
            $value2 = $stack->pop();
            $value1 = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcCallException("Not enough arguments for binary operator: {$operation->operator}");
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
                    $stack->push(($operator->callable)($value1, $value2));
                    break;
                }
                throw new Exceptions\CalcCallException("Undefined binary operator: {$operation->operator}");
        }
    }

    private function performUnaryOperator(Operation\UnaryOperator $operation, SplStack $stack): void
    {
        try {
            $value = $stack->pop();
        } catch (RuntimeException) {
            throw new Exceptions\CalcCallException("Not enough arguments for unary operator: {$operation->operator}");
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
                    $stack->push(($operator->callable)($value));
                    break;
                }
                throw new Exceptions\CalcCallException("Undefined unary operator: {$operation->operator}");
        }
    }
}
