<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Helpers\NormalizationHelper;

final class ConfigBuilder
{
    private static ?Config $defaultConfig = null;

    private array $functions = [];

    public static function default(): self
    {
        // add basic arithmetic functions
        return (new self())
            ->addFunctions(
                abs: abs(...),
            );
    }

    public static function defaultConfig(): Config
    {
        return self::$defaultConfig ??= self::default()->build();
    }

    public function build(): Config
    {
        return new Config(functions: $this->functions);
    }

    /**
     * @return $this
     */
    public function setFunctions(callable|Config\Func ...$functions): self
    {
        $this->functions = [];
        return $this->addFunctions(...$functions);
    }

    /**
     * @return array<string, Config\Func>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return $this
     */
    public function addFunctions(callable|Config\Func ...$functions): self
    {
        foreach ($functions as $name => $function) {
            if ($function instanceof Config\Func) {
                $this->addFunction($function);
            } else {
                $this->addFunctionFromCallable($name, $function);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addFunctionFromCallable(string $name, callable $callable): self
    {
        $this->functions[Helpers\NormalizationHelper::normalizeName($name)] = new Config\Func($name, $callable(...));
        return $this;
    }

    /**
     * @return $this
     */
    public function addFunction(Config\Func $func): self
    {
        $this->functions[Helpers\NormalizationHelper::normalizeName($func->name)] = $func;
        return $this;
    }

    /**
     * @return $this
     */
    public function removeFunctions(string|Config\Func ...$funcs): self
    {
        foreach ($funcs as $func) {
            if ($func instanceof Config\Func) {
                $func = $func->name;
            }
            unset($this->functions[NormalizationHelper::normalizeName($func)]);
        }
        return $this;
    }
}
