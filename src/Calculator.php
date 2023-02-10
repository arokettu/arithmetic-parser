<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Calculator
{
    private readonly array $operations;
    private readonly Config $config;

    public function __construct(
        iterable $operations = [],
        ?Config $config = null,
    ) {
        $this->setOperations(...$operations);
        $this->config = $config ?? ConfigBuilder::defaultConfig();
    }

    private function setOperations(Parser\Operation ...$operations): void
    {
        $this->operations = array_values($operations);
    }

    public static function parse(string $input, ?Config $config = null): self
    {
        return new self((new Parser())->parse($input), $config);
    }

    public static function evaluate(string $expression, ?Config $config = null, float ...$vars): float
    {
        return self::parse($expression, $config)->calc(...$vars);
    }

    public function calc(float ...$vars): float
    {
        $normalizedVars = [];

        foreach ($vars as $name => $value) {
            if (!\is_string($name)) {
                throw new \InvalidArgumentException('Invalid variable name: ' . $name);
            }
            $normalizedName = Helpers\NormalizationHelper::normalizeName($name);
            if (isset($normalizedVars[$normalizedName])) {
                throw new \InvalidArgumentException('Duplicate variable name: ' . $name);
            }
            $normalizedVars[$normalizedName] = $value;
        }

        $stack = new Stack();

        foreach ($this->operations as $operation) {
            switch ($operation->type) {
                case Parser\OperationType::NUMBER:
                    $stack->push(\floatval($operation->value));
                    break;
                case Parser\OperationType::VARIABLE:
                    $varName = $operation->value['normalized'];
                    if (!isset($normalizedVars[$varName])) {
                        throw new \RuntimeException("Variable {$operation->value['name']} is not defined");
                    }
                    $stack->push($normalizedVars[$varName]);
                    break;
                case Parser\OperationType::FUNCTION:
                    $this->performFunction($operation, $stack);
                    break;
                case Parser\OperationType::BINARY_OPERATOR:
                    $this->performBinaryOperator($operation, $stack);
                    break;
                case Parser\OperationType::UNARY_OPERATOR:
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

    private function performFunction(Parser\Operation $operation, Stack $stack): void
    {
        $funcName = $operation->value['normalized'];
        $value = $stack->pop();
        $func =
            $this->config->functions[$funcName] ??
            throw new \RuntimeException("Undefined function: {$operation->value['name']}");
        $stack->push(($func->callable)($value));
    }

    private function performBinaryOperator(Parser\Operation $operation, Stack $stack): void
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

    private function performUnaryOperator(Parser\Operation $operation, Stack $stack): void
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
