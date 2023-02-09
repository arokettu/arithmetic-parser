<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Calculator
{
    private readonly array $operations;

    public function __construct(
        Operation ...$operations,
    ) {
        $this->operations = $operations;
    }

    public static function parse(string $input): self
    {
        return new self(...(new Parser())->parse($input));
    }

    public static function evaluate(string $expression, float ...$vars): float
    {
        return self::parse($expression)->calc(...$vars);
    }

    public function calc(float ...$vars): float
    {
        $normalizedVars = [];

        foreach ($vars as $name => $value) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException('Invalid variable name: ' . $name);
            }
            $normalizedName = NormalizationHelper::normalizeName($name);
            if (isset($normalizedVars[$normalizedName])) {
                throw new \InvalidArgumentException('Duplicate variable name: ' . $name);
            }
            $normalizedVars[$normalizedName] = $value;
        }

        $stack = new Stack();

        foreach ($this->operations as $operation) {
            switch ($operation->type) {
                case OperationType::NUMBER:
                    $stack->push(\floatval($operation->value));
                    break;
                case OperationType::VARIABLE:
                    $varName = $operation->value['normalized'];
                    if (!isset($normalizedVars[$varName])) {
                        throw new \RuntimeException("Variable {$operation->value['name']} is not defined");
                    }
                    $stack->push($normalizedVars[$varName]);
                    break;
                case OperationType::FUNCTION:
                    $this->performFunction($operation, $stack);
                    break;
                case OperationType::BINARY_OPERATOR:
                    $this->performBinaryOperator($operation, $stack);
                    break;
                case OperationType::UNARY_OPERATOR:
                    $this->performUnaryOperator($operation, $stack);
                    break;
                default:
                    throw new \RuntimeException('Invalid operator');
            }
        }

        if (\count($stack) !== 1) {
            throw new \RuntimeException('Operator sequence is wrong');
        }

        return $stack->pop();
    }

    private function performFunction(Operation $operation, Stack $stack): void
    {
        $function = $operation->value['normalized'];

        $value = $stack->pop();

        switch ($function) {
            case 'ABS':
                $stack->push(abs($value));
                break;
            default:
                throw new \RuntimeException("Undefined function: {$operation->value['name']}");
        }
    }

    private function performBinaryOperator(Operation $operation, Stack $stack): void
    {
        $operator = $operation->value;

        $value2 = $stack->pop();
        $value1 = $stack->pop();

        switch ($operator) {
            case '+':
                $stack->push($value1 + $value2);
                break;
            case '-':
                $stack->push($value1 - $value2);
                break;
            case '*':
                $stack->push($value1 * $value2);
                break;
            case '/':
                $stack->push($value1 / $value2);
                break;
            default:
                throw new \RuntimeException("Undefined operator: {$operation->value['name']}");
        }
    }

    private function performUnaryOperator(Operation $operation, Stack $stack): void
    {
        $operator = $operation->value;

        $value = $stack->pop();

        switch ($operator) {
            case '-':
                $stack->push(-$value);
                break;
            default:
                throw new \RuntimeException("Undefined operator: {$operation->value['name']}");
        }
    }
}
