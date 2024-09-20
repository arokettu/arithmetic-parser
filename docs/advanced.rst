Advanced Use Cases
##################

.. highlight:: php

.. _lazy-calc:

Lazy Calculations
=================

Calculator vs LazyCalculator
----------------------------

The default calculator has some advantages over the lazy calculator:

* Works faster
* Non-recursive calculation, not limited by call stack
* Easier to debug missing variables and functions

Still, lazy calculator is a must if your variables may be missing or you need to guard against error conditions.

.. note::
    Lazy functions and operators can still be used in the default calculator,
    just their arguments will be pre-calculated.

LazyArgument
------------

``Arokettu\ArithmeticParser\Argument\LazyArgument``

LazyArgument is an interface that wraps inner calculations.
In ``LazyCalculator`` the inner calculations are not performed unless you call ``getValue()``.
In ``Calculator`` it's just a wrapper for the float value that would be passed normally.

Partial Execution
-----------------

The most useful application of lazy calculator is partial execution::

    <?php

    use Arokettu\ArithmeticParser\Calculator;
    use Arokettu\ArithmeticParser\LazyCalculator;

    var_dump(LazyCalculator::evaluate('if (true(), 123, 1 / 0)')); // 123
    var_dump(Calculator::evaluate('if (true(), 123, 1 / 0)')); // DivisionByZeroError

    var_dump(LazyCalculator::evaluate('a or b', a: 1)); // 1 (true)
    var_dump(Calculator::evaluate('a or b', a: 1)); // UndefinedVariableException

Error Handling
--------------

Since ``getValue()`` actually wraps calculation,
lazy functions and operators can also be used to detect errors in their subtrees.

Let's create a custom optional operator ``value?`` and a custom default operator ``value ?? default``::

    <?php

    use Arokettu\ArithmeticParser\Argument\LazyArgument;
    use Arokettu\ArithmeticParser\Calculator;
    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Exceptions\UndefinedVariableException;
    use Arokettu\ArithmeticParser\LazyCalculator;

    $config = Config::default();

    $config->addOperator(new Config\UnaryOperator(
        '?',
        function (LazyArgument $a) {
            try {
                return $a->getValue(); // if a value is defined, return it
            } catch (UndefinedVariableException) {
                return 0; // fall back to zero
            }
        },
        lazy: true,
    ));
    $config->addOperator(new Config\BinaryOperator(
        '??',
        function (LazyArgument $a, LazyArgument $b) {
            try {
                return $a->getValue(); // if a value is defined, return it
            } catch (UndefinedVariableException) {
                return $b->getValue(); // fall back to the second argument
            }
        },
        1_000_000, // top priority
        lazy: true,
    ));

    var_dump(LazyCalculator::evaluate('log(a, b ?? e())', $config, a: 1024, b: 2)); // 10
    var_dump(LazyCalculator::evaluate('log(a, b ?? e())', $config, a: 1024)); // 6.9314...
    // default config equivalent:
    var_dump(LazyCalculator::evaluate('log(a, if(defined(b), b, e()))', a: 1024)); // 6.9314...

    // Default calculator will still accept a lazy operation
    var_dump(Calculator::evaluate('log(a, b ?? e())', $config, a: 1024, b: 2)); // 10
    // but the actual fallback won't work
    var_dump(Calculator::evaluate('log(a, b ?? e())', $config, a: 1024)); // UndefinedVariableException

    // Lazy unary was created specifically because of error handling possibility
    var_dump(LazyCalculator::evaluate('a? + b? + c?', $config)); // 0
    var_dump(LazyCalculator::evaluate('a? + b? + c?', $config, a: 1, c: 3)); // 4
    var_dump(LazyCalculator::evaluate('a? + b? + c?', $config, a: 1, b: 2, c: 3)); // 6

    // Default calculator will still accept a lazy operation
    var_dump(Calculator::evaluate('a? + b? + c?', $config, a: 1, b: 2, c: 3)); // 6
    // but the actual optional won't work
    var_dump(Calculator::evaluate('a? + b? + c?', $config)); // UndefinedVariableException

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
