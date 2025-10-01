<?php

/**
 * @copyright 2023 Anton Smirnov
 * @license MIT https://spdx.org/licenses/MIT.html
 */

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Exceptions\UndefinedVariableException;
use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class Config
{
    private static Config|null $default = null;

    private array $functions = [];
    private array $operators = [];

    private static function buildDefault(): self
    {
        // add basic arithmetic functions
        return (new self())
            ->addFunctionsFromCallables(
                // generic
                abs: abs(...),
                exp: exp(...),
                log: log(...),
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
                round: static fn (float $num, float $precision = 0) => round($num, \intval($precision)),
                // conversion
                deg2rad: deg2rad(...),
                rad2deg: rad2deg(...),
                // constants
                pi: static fn () => M_PI,
                e: static fn () => M_E,
                true: static fn () => 1,
                false: static fn () => 0,
                nan: static fn () => NAN,
                inf: static fn () => INF,
            )->addFunctionsFromCallables(
                lazy: true,
                // compare
                if: static fn (
                    Argument\LazyArgument $check,
                    Argument\LazyArgument $then,
                    Argument\LazyArgument $else,
                ) => $check->getValue() ? $then->getValue() : $else->getValue(),
                defined: static function (Argument\LazyArgument $argument): float {
                    try {
                        $argument->getValue();
                        return 1;
                    } catch (UndefinedVariableException) {
                        return 0;
                    }
                },
            )->addOperators(
                // arithmetic

                new Config\BinaryOperator(
                    '*',
                    static fn (float $a, float $b): float => $a * $b,
                    Config\BinaryPriority::MUL,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '/',
                    static fn (float $a, float $b): float => $a / $b,
                    Config\BinaryPriority::MUL,
                    Config\BinaryAssoc::LEFT,
                ),
                // comparison
                new Config\BinaryOperator(
                    '<',
                    static fn (float $a, float $b): float => \intval($a < $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '>',
                    static fn (float $a, float $b): float => \intval($a > $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '<=',
                    static fn (float $a, float $b): float => \intval($a <= $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '>=',
                    static fn (float $a, float $b): float => \intval($a >= $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '=',
                    static fn (float $a, float $b): float => \intval($a == $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '==',
                    static fn (float $a, float $b): float => \intval($a == $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '!=',
                    static fn (float $a, float $b): float => \intval($a != $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '<>',
                    static fn (float $a, float $b): float => \intval($a != $b),
                    Config\BinaryPriority::COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                // logic
                new Config\BinaryOperator(
                    'and',
                    static fn (Argument\LazyArgument $a, Argument\LazyArgument $b): float =>
                        \intval($a->getValue() && $b->getValue()),
                    Config\BinaryPriority::AND,
                    Config\BinaryAssoc::LEFT,
                    lazy: true,
                ),
                new Config\BinaryOperator(
                    'AND',
                    static fn (Argument\LazyArgument $a, Argument\LazyArgument $b): float =>
                        \intval($a->getValue() && $b->getValue()),
                    Config\BinaryPriority::AND,
                    Config\BinaryAssoc::LEFT,
                    lazy: true,
                ),
                new Config\BinaryOperator(
                    'or',
                    static fn (Argument\LazyArgument $a, Argument\LazyArgument $b): float =>
                        \intval($a->getValue() || $b->getValue()),
                    Config\BinaryPriority::OR,
                    Config\BinaryAssoc::LEFT,
                    lazy: true,
                ),
                new Config\BinaryOperator(
                    'OR',
                    static fn (Argument\LazyArgument $a, Argument\LazyArgument $b): float =>
                        \intval($a->getValue() || $b->getValue()),
                    Config\BinaryPriority::OR,
                    Config\BinaryAssoc::LEFT,
                    lazy: true,
                ),
                new Config\UnaryOperator(
                    'not',
                    static fn (float $a): float => \intval(!$a),
                    Config\UnaryPos::PREFIX,
                ),
                new Config\UnaryOperator(
                    'NOT',
                    static fn (float $a): float => \intval(!$a),
                    Config\UnaryPos::PREFIX,
                ),
            );
    }

    public static function default(): Config
    {
        return clone (self::$default ??= self::buildDefault());
    }

    /**
     * @return $this
     */
    public function setFunctions(Config\Func ...$functions): self
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
    public function addFunctions(Config\Func ...$functions): self
    {
        foreach ($functions as $function) {
            $this->addFunction($function);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addFunctionsFromCallables(bool $lazy = false, callable ...$functions): self
    {
        foreach ($functions as $name => $function) {
            $this->addFunctionFromCallable($name, $function(...), $lazy);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addFunctionFromCallable(string $name, callable $callable, bool $lazy = false): self
    {
        return $this->addFunction(new Config\Func($name, $callable(...), $lazy));
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
    public function removeFunctions(string ...$funcs): self
    {
        foreach ($funcs as $func) {
            unset($this->functions[NameHelper::normalizeFunc($func)]);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function removeFunction(string $func): self
    {
        return $this->removeFunctions($func);
    }

    /**
     * @return $this
     */
    public function clearFunctions(): self
    {
        $this->setFunctions();
        return $this;
    }

    /**
     * @return $this
     */
    public function setOperators(Config\Operator ...$operators): self
    {
        $this->operators = [];
        return $this->addOperators(...$operators);
    }

    /**
     * @return array<string, Config\Operator>
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * @return $this
     */
    public function addOperators(Config\BinaryOperator|Config\UnaryOperator ...$operators): self
    {
        foreach ($operators as $operator) {
            $this->addOperator($operator);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addOperator(Config\BinaryOperator|Config\UnaryOperator $operator): self
    {
        // this method also does a narrower type check for unrecognized operator classes
        $this->operators[$operator->symbol] = $operator;
        return $this;
    }

    /**
     * @return $this
     */
    public function removeOperators(string ...$operators): self
    {
        foreach ($operators as $operator) {
            unset($this->operators[$operator]);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function removeOperator(string $operator): self
    {
        return $this->removeOperators($operator);
    }

    /**
     * @return $this
     */
    public function clearOperators(): self
    {
        $this->setOperators();
        return $this;
    }
}
