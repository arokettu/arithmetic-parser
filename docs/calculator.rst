Calculator
##########

``Arokettu\ArithmeticParser\Calculator`` class is used to interpret the arithmetic expression.


``Calculator::parse()``
=======================

Parses the expression and creates an instance of calculator for it.

``calc()``
==========

Evaluates the expression with given variables (if any).

``Calculator::evaluate()``
==========================

``Calculator::evaluate($expression, $config, ...$vars)`` is a shorthand for
``Calculator::parse($expression, $config)->calc(...$vars)``
