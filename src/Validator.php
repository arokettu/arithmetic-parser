<?php

declare(strict_types=1);

namespace Arokettu\ArithmeticParser;

use Arokettu\ArithmeticParser\Exceptions\ValidationException;
use Arokettu\ArithmeticParser\Helpers\NameHelper;

final class Validator
{
    /**
     * @param list<string> $variables
     * @return iterable<Validator\Warning>
     */
    private static function doValidate(Parser\Parsed $parsed, Config $config, array $variables): iterable
    {
        $variables = array_flip(array_map(NameHelper::normalizeVar(...), $variables));
        $functions = $config->getFunctions();

        // all variables present
        $missingVariables = array_diff_key($parsed->variables, $variables);
        if ($missingVariables !== []) {
            yield new Validator\MissingVariablesWarning($missingVariables);
        }

        $missingFunctions = array_diff_key($parsed->functions, $functions);
        if ($missingFunctions !== []) {
            yield new Validator\MissingFunctionsWarning($missingFunctions);
        }

        $providedFunctions = array_intersect_key($parsed->functions, $functions);
        foreach ($providedFunctions as $name => $operation) {
            $declaration = $functions[$name];

            if ($operation->arity < $declaration->minArity) {
                yield new Validator\MissingFunctionArgumentsWarning($declaration, $operation);
            }
        }
    }

    /**
     * @param list<string> $variables
     * @return list<Validator\Warning>
     */
    public static function validate(Parser\Parsed $parsed, Config $config, array $variables): array
    {
        return [...self::doValidate($parsed, $config, $variables)];
    }

    /**
     * @param list<string> $variables
     * @param list<Validator\Warning> $warnings
     */
    public static function isValid(
        Parser\Parsed $parsed,
        Config $config,
        array $variables,
        array|null &$warnings = null,
    ): bool {
        if (\func_num_args() > 3) {
            // $warnings passed - collect all warnings
            $warnings = self::validate($parsed, $config, $variables);
            return $warnings === [];
        } else {
            // no warnings passed - fail early
            /** @noinspection PhpLoopNeverIteratesInspection */
            foreach (self::doValidate($parsed, $config, $variables) as $_) {
                return false;
            }
            return true;
        }
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
