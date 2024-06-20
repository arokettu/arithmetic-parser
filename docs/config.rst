Configuration
#############

.. highlight:: php

``Arokettu\ArithmeticParser\Config`` class is used to configure the calculator and the parser.

Config is mutable but it is cloned internally when passed to prevent external alterations.

``Config::default()``
=====================

The default preset used when no config is specified.

Functions
=========

.. versionadded:: 1.1.0 removeFunction() and clearFunctions()

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
`round <https://www.php.net/manual/en/function.round.php>`__,
`deg2rad <https://www.php.net/manual/en/function.deg2rad.php>`__,
`rad2deg <https://www.php.net/manual/en/function.rad2deg.php>`__,
`pi <https://www.php.net/manual/en/math.constants.php#constant.m-pi>`,
`e <https://www.php.net/manual/en/math.constants.php#constant.m-e>`,
`if(if, then, else)`.

.. note::
    ``if()`` is a regular function and therefore is not lazy.
    For example, ``if (a = 0, 0, 1/a)`` will result in division by zero if ``a = 0``.

You can:

* Replace functions with your own list::

    <?php
    $config->setFunctions(myfunc2: fn ($a) => a ** 2);
* Add new functions::

    <?php
    $config->addFunctions(myfunc3: fn ($a) => a ** 3);
* Remove functions::

    <?php
    $config->removeFunctions('acos', 'asin');
    $config->removeFunction('acos'); // semantic alias for removeFunctions('acos')
    $config->clearFunctions(); // remove all functions

Operators
=========

.. versionadded:: 1.1.0 removeOperator() and clearOperators()

Operators can be unary and binary.
Operator symbol can be any string without digits.
Be wary when using latin character based operators, they are case-sensitive and may shadow variables and functions.

Default operators:

* ``+``, ``-`` in both unary and binary form. They are built-in and are not configurable.
* ``*``, ``/``.

You can:

* Replace operators with your own list::

    <?php
    $config->setOperators(
        new BinaryOperator('×', fn ($a, $b) => $a * $b, BinaryOperator::PRIORITY_MUL),
        new BinaryOperator('÷', fn ($a, $b) => $a / $b, BinaryOperator::PRIORITY_MUL),
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
