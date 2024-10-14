Configuration
#############

.. highlight:: php

``Arokettu\ArithmeticParser\Config`` class is used to configure the calculator and the parser.

Config is mutable but it is cloned internally when passed to prevent external alterations.

.. warning::
    Calling parser and calculator with different config objects is not supported
    unless only functions were added.

``Config::default()``
=====================

The default preset used when no config is specified.

.. _calc-config-funcs:

Functions
=========

.. versionadded:: 1.1 removeFunction() and clearFunctions()
.. versionchanged:: 2.0 Functions can accept any number of arguments
.. versionadded:: 2.0 ``pi()``, ``e()``, ``if()``
.. versionchanged:: 2.0 ``log()`` now also has ``base`` optional param
.. versionadded:: 3.0 ``defined()``, precision param for ``round()``, ``true()``, ``false()``, ``nan()``, ``inf()``
.. versionchanged::
    3.0 ``setFunctions()`` and ``addFunctions()`` no longer accept callables,
    ``addFunctionsFromCallables()`` was added to handle them

The function must be a callable that accepts float arguments.

Default functions:
`abs <https://www.php.net/manual/en/function.abs.php>`__,
`exp <https://www.php.net/manual/en/function.exp.php>`__,
`log(num, base = e()) <https://www.php.net/manual/en/function.log.php>`__,
`log10 <https://www.php.net/manual/en/function.log10.php>`__,
`sqrt <https://www.php.net/manual/en/function.sqrt.php>`__,
`acos <https://www.php.net/manual/en/function.acos.php>`__,
`asin <https://www.php.net/manual/en/function.asin.php>`__,
`atan <https://www.php.net/manual/en/function.atan.php>`__,
`cos <https://www.php.net/manual/en/function.cos.php>`__,
`sin <https://www.php.net/manual/en/function.sin.php>`__,
`tan <https://www.php.net/manual/en/function.tan.php>`__,
`acosh <https://www.php.net/manual/en/function.acosh.php>`__,
`asinh <https://www.php.net/manual/en/function.asinh.php>`__,
`atanh <https://www.php.net/manual/en/function.atanh.php>`__,
`cosh <https://www.php.net/manual/en/function.cosh.php>`__,
`sinh <https://www.php.net/manual/en/function.sinh.php>`__,
`tanh <https://www.php.net/manual/en/function.tanh.php>`__,
`ceil <https://www.php.net/manual/en/function.ceil.php>`__,
`floor <https://www.php.net/manual/en/function.floor.php>`__,
`round(num, precision = 0) <https://www.php.net/manual/en/function.round.php>`__,
`deg2rad <https://www.php.net/manual/en/function.deg2rad.php>`__,
`rad2deg <https://www.php.net/manual/en/function.rad2deg.php>`__,
`pi <https://www.php.net/manual/en/math.constants.php#constant.m-pi>`__,
`e <https://www.php.net/manual/en/math.constants.php#constant.m-e>`__,
`true <https://www.php.net/manual/en/language.types.boolean.php>`__,
`false <https://www.php.net/manual/en/language.types.boolean.php>`__,
`nan <https://www.php.net/manual/en/math.constants.php#constant.nan>`__,
`inf <https://www.php.net/manual/en/math.constants.php#constant.inf>`__.

Default lazy functions:
    * ``if(if, then, else)``. Regular ``if`` expression
    * ``defined(variable)``. Returns 1 if variable is defined and 0 if not

.. warning::
    Lazy functions act like regular functions in the default calculator.
    For example, ``if (a = 0, 0, 1/a)`` will result in division by zero if ``a = 0``.

You can:

* Replace functions with your own list::

    <?php
    $config->setFunctions(
        new Config\Func('myfunc2', fn (float $a) => $a ** 2)
    )
* Add new functions::

    <?php
    // by object
    $config->setFunctions(
        new Config\Func('myfunc3', fn (float $a) => $a ** 3)
    )
    // or by callable
    $config->addFunctionsFromCallables(myfunc4: fn (float $a) => $a ** 4);
* Remove functions::

    <?php
    $config->removeFunctions('acos', 'asin');
    $config->removeFunction('acos'); // semantic alias for removeFunctions('acos')
    $config->clearFunctions(); // remove all functions

Callable must accept all floats for a regular function or all instances of ``LazyArgument`` for a lazy function.
Lazy functions can be used with the default calculator, but they will act like regular functions in that case.

For example, function that returns its first nonzero argument::

    <?php

    declare(strict_types=1);

    use Arokettu\ArithmeticParser\Argument\LazyArgument;
    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\LazyCalculator;

    $config = Config::default();

    $config->addFunctionFromCallable('first_nonzero', function (LazyArgument ...$args) {
        foreach ($args as $a) {
            $value = $a->getValue();
            if ($value !== 0.0) {
                return $value;
            }
        }
        return 0;
    }, true);

    var_dump(LazyCalculator::evaluate(
        'first_nonzero(a, b, c, notafunc(d) / 0)', $config,
        a: 0, b: 0, c: 3
    )); // 3

Operators
=========

.. versionadded:: 1.1 removeOperator() and clearOperators()
.. versionadded:: 2.0 ``<``, ``>``, ``<=``, ``>=``, ``=``, ``==``, ``<>``, ``!=``, ``and``, ``AND``, ``or``, ``OR``
.. versionadded:: 3.0 ``not`` (also ``NOT``)

Operators can be unary and binary.
Operator symbol can be any string without digits.
Be wary when using latin character based operators, they are case-sensitive and may shadow variables and functions.

Default operators:

* ``+``, ``-`` in both unary and binary form. They are built-in and are not configurable.
* ``*``, ``/``,
  ``<``, ``>``, ``<=``, ``>=``,
  ``=`` (also ``==``), ``<>`` (also ``!=``),
  ``and`` (also ``AND``), ``or`` (also ``OR``),
  ``not`` (also ``NOT``).

.. note:: ``and/AND`` and ``or/OR``) are lazy like in most programming languages

You can:

* Replace operators with your own list::

    <?php
    $config->setOperators(
        new BinaryOperator('×', fn (float $a, float $b) => $a * $b, BinaryOperator::PRIORITY_MUL),
        new BinaryOperator('÷', fn (float $a, float $b) => $a / $b, BinaryOperator::PRIORITY_MUL),
    );

* Add new operators::

    <?php
    // assuming you have factorial() defined
    $config->addOperators(
        new BinaryOperator('^', pow(...), BinaryOperator::PRIORITY_POW, BinaryAssoc::RIGHT),
        new UnaryOperator('!', factorial(...), UnaryPos::POSTFIX),
    );

* Remove operators::

    <?php
    // remove any custom or built-in operators except for + and -
    $config->removeOperators('*', '/');
    // you cannot divide by zero if you cannot divide
    $config->removeOperator('/'); // semantic alias for removeOperators('/')
    // leave only + and -
    $config->clearOperators(); // + and - are handled specially and can't be removed

Like functions, operators can be lazy, in that case callables must accept instances of ``LazyArgument`` as arguments.

For example, OR operator that returns the actual value of the first truth-y argument::

    <?php

    declare(strict_types=1);

    use Arokettu\ArithmeticParser\Argument\LazyArgument;
    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Config\BinaryPriority;
    use Arokettu\ArithmeticParser\LazyCalculator;

    $config = Config::default();

    $config->addOperator(new Config\BinaryOperator(
        '||',
        fn (LazyArgument $a, LazyArgument $b)
            => $a->getValue() ?: $b->getValue(),
        BinaryPriority::OR,
        Config\BinaryAssoc::LEFT,
        true,
    ));

    var_dump(LazyCalculator::evaluate('a || b', $config, a: 0, b: 12)); // 12
    var_dump(LazyCalculator::evaluate('a || b / 0', $config, a: 123)); // 123
