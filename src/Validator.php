<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Exceptions\ValidationException;
use Arokettu\ArithmeticParser\Helpers\NameHelper;
use Arokettu\ArithmeticParser\Validator\AbstractWarning;

final class Validator
{
    /**
     * @param list<string> $variables
     * @return iterable<Validator\AbstractWarning>
     */
    private static function doValidate(Parser\Parsed $parsed, Config $config, array $variables): iterable
    {
        $variables = array_flip(array_map(NameHelper::normalizeVar(...), $variables));

        // all variables present
        $missingVariables = array_diff_key($parsed->variables, $variables);
        if ($missingVariables !== []) {
            yield new Validator\MissingVariablesWarning($missingVariables);
        }
    }

    /**
     * @param list<string> $variables
     * @return list<Validator\AbstractWarning>
     */
    public static function validate(Parser\Parsed $parsed, Config $config, array $variables): array
    {
        return [...self::doValidate($parsed, $config, $variables)];
    }

    /**
     * @param list<string> $variables
     */
    public static function isValid(Parser\Parsed $parsed, Config $config, array $variables, AbstractWarning|null &$warning = null): bool
    {
        foreach (self::doValidate($parsed, $config, $variables) as $w) {
            $warning = $w;
            return false;
        }
        return true;
    }

    /**
     * @param list<string> $variables
     * @throws ValidationException
     */
    public static function assertValid(Parser\Parsed $parsed, Config $config, array $variables): void
    {
        foreach (self::doValidate($parsed, $config, $variables) as $warning) {
            throw $warning->toException();
        }
    }
}
