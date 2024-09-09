<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

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
                round: fn (float $num) => round($num),
                // conversion
                deg2rad: deg2rad(...),
                rad2deg: rad2deg(...),
                // constants
                pi: fn () => M_PI,
                e: fn () => M_E,
            )->addFunctionsFromCallables(
                lazy: true,
                // compare
                if: fn (Argument\LazyArgument $check, Argument\LazyArgument $then, Argument\LazyArgument $else) =>
                    $check->getValue() ?
                    $then->getValue() :
                    $else->getValue(),
            )->addOperators(
                // arithmetic

                new Config\BinaryOperator(
                    '*',
                    fn (float $a, float $b): float => $a * $b,
                    Config\BinaryOperator::PRIORITY_MUL,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '/',
                    fn (float $a, float $b): float => $a / $b,
                    Config\BinaryOperator::PRIORITY_MUL,
                    Config\BinaryAssoc::LEFT,
                ),
                // comparison
                new Config\BinaryOperator(
                    '<',
                    fn (float $a, float $b): float => \intval($a < $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '>',
                    fn (float $a, float $b): float => \intval($a > $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '<=',
                    fn (float $a, float $b): float => \intval($a <= $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '>=',
                    fn (float $a, float $b): float => \intval($a >= $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '=',
                    fn (float $a, float $b): float => \intval($a == $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '==',
                    fn (float $a, float $b): float => \intval($a == $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '!=',
                    fn (float $a, float $b): float => \intval($a != $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    '<>',
                    fn (float $a, float $b): float => \intval($a != $b),
                    Config\BinaryOperator::PRIORITY_COMPARE,
                    Config\BinaryAssoc::LEFT,
                ),
                // logic
                new Config\BinaryOperator(
                    'and',
                    fn (float $a, float $b): float => \intval($a && $b),
                    Config\BinaryOperator::PRIORITY_AND,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    'AND',
                    fn (float $a, float $b): float => \intval($a && $b),
                    Config\BinaryOperator::PRIORITY_AND,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    'or',
                    fn (float $a, float $b): float => \intval($a || $b),
                    Config\BinaryOperator::PRIORITY_OR,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\BinaryOperator(
                    'OR',
                    fn (float $a, float $b): float => \intval($a || $b),
                    Config\BinaryOperator::PRIORITY_OR,
                    Config\BinaryAssoc::LEFT,
                ),
                new Config\UnaryOperator(
                    'not',
                    fn (float $a): float => \intval(!$a),
                    Config\UnaryPos::PREFIX,
                ),
                new Config\UnaryOperator(
                    'NOT',
                    fn (float $a): float => \intval(!$a),
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
