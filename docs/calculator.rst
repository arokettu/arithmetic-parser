Calculator
##########

.. versionadded:: 3.0 LazyCalculator

``Arokettu\ArithmeticParser\Calculator`` class is used to interpret the arithmetic expression.

``Arokettu\ArithmeticParser\LazyCalculator`` class also supports lazy expressions, see :ref:`lazy-calc`.

``Calculator::parse()`` / ``LazyCalculator::parse()``
=====================================================

Parses the expression and creates an instance of calculator for it.

``calc()``
==========

Evaluates the expression with given variables (if any).

``Calculator::evaluate()`` / ``LazyCalculator::evaluate()``
===========================================================

``Calculator::evaluate($expression, $config, ...$vars)`` is a shorthand for
``Calculator::parse($expression, $config)->calc(...$vars)``
