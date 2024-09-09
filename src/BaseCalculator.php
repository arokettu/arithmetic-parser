<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

trait BaseCalculator
{
    /**
     * @throws Exceptions\CalcCallException
     */
    abstract public function calc(float ...$vars): float;

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
        Config|null $config = null,
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
    public static function parse(string $input, Config|null $config = null): static
    {
        return new static((new Parser($config))->parse($input)->operations, $config);
    }

    /**
     * @throws Exceptions\ParseException
     * @throws Exceptions\CalcCallException
     */
    public static function evaluate(string $expression, Config|null $config = null, float ...$vars): float
    {
        return self::parse($expression, $config)->calc(...$vars);
    }

}