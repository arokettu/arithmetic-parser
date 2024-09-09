<?php

namespace Arokettu\ArithmeticParser\Argument;

interface LazyArgument
{
    public function getValue(): float;
}
