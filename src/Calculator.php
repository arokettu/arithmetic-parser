<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use SplStack;

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
        $this->config = $config ? clone $config : Config::default();
    }

    private function setOperations(Operation\Operation ...$operations): void
    {
        /** @psalm-suppress InaccessibleProperty This method is called from the constructor only */
        $this->operations = array_values($operations);
    }

    /**
     * @throws Exceptions\ParseException
     */
    public static function parse(string $input, ?Config $config = null): self
    {
        return new self((new Parser($config))->parse($input)->operations, $config);
    }

    /**
     * @throws Exceptions\ParseException
     * @throws Exceptions\CalcCallException
     */
    public static function evaluate(string $expression, ?Config $config = null, float ...$vars): float
    {
        return self::parse($expression, $config)->calc(...$vars);
    }

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
        $value = $stack->pop();
        $func =
            $this->config->getFunctions()[$operation->normalizedName] ??
            throw new Exceptions\CalcCallException("Undefined function: {$operation->name}");
        $stack->push(($func->callable)($value));
    }

    private function performBinaryOperator(Operation\BinaryOperator $operation, SplStack $stack): void
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
        $value = $stack->pop();

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
