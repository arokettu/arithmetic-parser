Configuration
#############

``Arokettu\ArithmeticParser\Config`` and ``Arokettu\ArithmeticParser\ConfigBuilder``
classes are used to configure the calculator and the parser (in future).

Config is an immutable object and ConfigBuilder is a user-friendly way to set up options.

ConfigBuilder
=============

``build()``
-----------

A method to create a Config object with configured parameters.

``ConfigBuilder::default()``
----------------------------

The default preset used when no config is specified.

``ConfigBuilder::defaultConfig()``
----------------------------------

A prebuilt instance of the Config object for the default preset.
A shortcut for ``ConfigBuilder::default()->build()`` with a cached instance.

Functions
=========

The only configurable thing for now is a set of functions.
The function must be a callable that accepts a single float argument.

Default functions:
`abs <https://www.php.net/manual/en/function.abs.php>`__,
`exp <https://www.php.net/manual/en/function.exp.php>`__,
`log <https://www.php.net/manual/en/function.log.php>`__,
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
`rad2deg <https://www.php.net/manual/en/function.rad2deg.php>`__.

You can:

* Replace functions with your own list:
  ``$builder->setFunctions(['myfunc2' => fn ($a) => a ** 2]);``
* Add new functions:
  ``$builder->addFunctions(['myfunc3' => fn ($a) => a ** 3]);``
* Remove functions:
  ``$builder->removeFunctions('acos', 'asin');``
