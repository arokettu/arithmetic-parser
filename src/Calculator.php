<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Ds\Stack;

final class Calculator
{
    /**
     * @var array<int, Operation\Operation>
     */
    private readonly array $operations;
    private readonly Config $config;

    /**
     * @param iterable<int, Operation\Operation> $operations
     * @param Config|null $config
     */
    public function __construct(
        iterable $operations = [],
        ?Config $config = null,
    ) {
        $this->setOperations(...$operations);
        $this->config = $config ?? ConfigBuilder::defaultConfig();
    }

    private function setOperations(Operation\Operation ...$operations): void
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
            $normalizedName = Helpers\NameHelper::normalizeVar($name);
            if (isset($normalizedVars[$normalizedName])) {
                throw new \InvalidArgumentException('Duplicate variable name: ' . $name);
            }
            $normalizedVars[$normalizedName] = $value;
        }

        $stack = new Stack();

        foreach ($this->operations as $operation) {
            switch (true) {
                case $operation instanceof Operation\Number:
                    $stack->push($operation->value);
                    break;
                case $operation instanceof Operation\Variable:
                    if (!isset($normalizedVars[$operation->normalizedName])) {
                        throw new \RuntimeException("Variable {$operation->name} is not defined");
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
                    throw new \RuntimeException('Invalid operator');
            }
        }

        if (\count($stack) !== 1) {
            throw new \RuntimeException('Operator sequence is wrong');
        }

        return $stack->pop();
    }

    private function performFunction(Operation\FunctionCall $operation, Stack $stack): void
    {
        $value = $stack->pop();
        $func =
            $this->config->functions[$operation->normalizedName] ??
            throw new \RuntimeException("Undefined function: {$operation->name}");
        $stack->push(($func->callable)($value));
    }

    private function performBinaryOperator(Operation\BinaryOperator $operation, Stack $stack): void
    {
        $value2 = $stack->pop();
        $value1 = $stack->pop();

        switch ($operation->operator) {
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
                throw new \RuntimeException("Undefined operator: {$operation->operator}");
        }
    }

    private function performUnaryOperator(Operation\UnaryOperator $operation, Stack $stack): void
    {
        $value = $stack->pop();

        switch ($operation->operator) {
            case '-':
                $stack->push(-$value);
                break;
            default:
                throw new \RuntimeException("Undefined operator: {$operation->operator}");
        }
    }
}
