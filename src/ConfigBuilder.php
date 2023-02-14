<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class ConfigBuilder
{
    private static ?Config $defaultConfig = null;

    private array $functions = [];

    public static function default(): self
    {
        // add basic arithmetic functions
        return (new self())
            ->addFunctions(
                // generic
                abs: abs(...),
                exp: exp(...),
                log: fn (float $num) => log($num),
                log10: log10(...),
                sqrt: sqrt(...),
                // trigonometric functions
                acos: acos(...),
                asin: asin(...),
                atan: atan(...),
                cos: cos(...),
                sin: sin(...),
                tan: tan(...),
                // hyperbolic functions
                acosh: acosh(...),
                asinh: asinh(...),
                atanh: atanh(...),
                cosh: cosh(...),
                sinh: sinh(...),
                tanh: tanh(...),
                // rounding
                ceil: ceil(...),
                floor: floor(...),
                round: fn (float $num) => round($num),
                // conversion
                deg2rad: deg2rad(...),
                rad2deg: rad2deg(...),
            );
    }

    public static function fromConfig(Config $config): self
    {
        $builder = new self();
        $builder->functions = $config->functions;
        return $builder;
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
                $this->addFunctionFromCallable($name, $function(...));
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addFunctionFromCallable(string $name, callable $callable): self
    {
        return $this->addFunction(new Config\Func($name, $callable(...)));
    }

    /**
     * @return $this
     */
    public function addFunction(Config\Func $func): self
    {
        $this->functions[$func->normalizedName] = $func;
        return $this;
    }

    /**
     * @return $this
     */
    public function removeFunctions(string|Config\Func ...$funcs): self
    {
        foreach ($funcs as $func) {
            if ($func instanceof Config\Func) {
                unset($this->functions[$func->normalizedName]);
            } else {
                unset($this->functions[NameHelper::normalizeFunc($func)]);
            }
        }
        return $this;
    }
}
