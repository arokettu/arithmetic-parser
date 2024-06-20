<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Tests;

use Arokettu\ArithmeticParser\Config;
use DomainException;
use PHPUnit\Framework\TestCase;

class CommonOperatorsTest extends TestCase
{
    public function testNoEmptyOperator(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Operator symbol must not be an empty string');

        new Config\BinaryOperator('', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD);
    }

    public function testNoPlusOperator(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('+ and - are reserved and cannot be configured');

        new Config\BinaryOperator('+', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD);
    }

    public function testNoBracketOperator(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Brackets ( and ) are reserved and cannot be configured');

        new Config\BinaryOperator('(', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD);
    }

    public function testNoCommaOperator(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Comma (,) is reserved and cannot be configured');

        new Config\BinaryOperator(',', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD);
    }

    public function testNoForbiddenCharsOperator(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Operator symbol must not contain digits, dots, and spaces');

        new Config\BinaryOperator('0 .', fn ($a, $b) => $a + $b, Config\BinaryOperator::PRIORITY_ADD);
    }
}
