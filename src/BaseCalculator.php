<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

trait BaseCalculator
{
    /**
     * @throws Exceptions\CalcCallException
     * @throws Exceptions\CalcConfigException
     */
    abstract public function calc(float ...$vars): float;

    /**
     * @var array<int, Operation\Operation>
     */
    private readonly array $operations;
    private readonly Config $config;

    /**
     * @param iterable<int, Operation\Operation>|Parser\Parsed $operations
     * @param Config|null $config
     */
    public function __construct(
        iterable|Parser\Parsed $operations = [],
        Config|null $config = null,
    ) {
        if ($operations instanceof Parser\Parsed) {
            $operations = $operations->operations;
        }

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
        return new static((new Parser($config))->parse($input), $config);
    }

    /**
     * @throws Exceptions\ParseException
     * @throws Exceptions\CalcConfigException
     */
    public static function evaluate(string $expression, Config|null $config = null, float ...$vars): float
    {
        return self::parse($expression, $config)->calc(...$vars);
    }

    private function normalizeVars(array $vars): array
    {
        $normalizedVars = [];

        foreach ($vars as $name => $value) {
            if (!\is_string($name)) {
                throw new Exceptions\CalcConfigException('Invalid variable name: ' . $name);
            }
            $normalizedName = Helpers\NameHelper::normalizeVar($name);
            if (isset($normalizedVars[$normalizedName])) {
                throw new Exceptions\CalcConfigException('Duplicate variable name: ' . $name);
            }
            $normalizedVars[$normalizedName] = $value;
        }

        return $normalizedVars;
    }
}
