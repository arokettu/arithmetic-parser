<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser\Exceptions;

use Doctrine\Common\Lexer\Token;

final class ParseException extends \DomainException implements BaseException
{
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly int $position = 0,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromToken(string $message, Token $token): self
    {
        return new self($message . ' at position ' . $token->position, position: $token->position);
    }
}
