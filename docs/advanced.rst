Advanced Use Cases
##################

.. highlight:: php

.. _lazy-calc:

Lazy Calculations
=================

Dynamic Functions
=================

Since, unlike operators, functions are not resolved by the parser,
you can dynamically add missing functions before the actual calculation::

    <?php

    declare(strict_types=1);

    use Arokettu\ArithmeticParser\Calculator;
    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Parser;
    use Arokettu\ArithmeticParser\Validator;

    $config = Config::default();

    $parser = new Parser($config);
    $parsed = $parser->parse('log2(2048) + log3(27)');

    $warnings = Validator::validate($parsed, $config, []);

    foreach ($warnings as $w) {
        // find the warning about missing functions
        if ($w instanceof Validator\MissingFunctionsWarning) {
            foreach ($w->missingFunctions as $f) {
                // add logarithm function based on base value in the name
                if (str_starts_with($f->normalizedName, 'LOG')) {
                    $base = \intval(substr($f->normalizedName, 3));
                    $config->addFunctionFromCallable($f->name, fn ($a) => log($a, $base));
                }
            }
            break;
        }
    }

    var_dump((new Calculator($parsed->operations, $config))->calc()); // 14

.. note:: It is strictly recommended not to change anything in the config except adding missing functions.
