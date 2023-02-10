<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

final class Config
{
    /** @var array<string, Config\Func> */
    public readonly array $functions;

    public function __construct(iterable $functions = [])
    {
        $this->setFunctions(...$functions);
    }

    private function setFunctions(Config\Func ...$functions): void
    {
        $this->functions = array_reduce($functions, function (array $fs, Config\Func $f) {
            $fs[Helpers\NameHelper::normalizeVar($f->name)] = $f;
            return $fs;
        }, []);
    }
}
