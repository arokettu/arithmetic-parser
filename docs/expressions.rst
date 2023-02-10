Expressions
###########

Supported Elements
==================

Supported expressions can include:

* Unary ``+`` and ``-``.
* Binary ``+``, ``-``, ``*``, ``/``.
* Numbers, obviously.
  All numbers are cast to float internally.
* Functions.
  Function names are case insensitive alphanumeric strings that do not start with a number.
  Functions accept a single parameter.
  Custom functions can be created with a Config object.
* Variables.
  Variable names are case insensitive alphanumeric strings that do not start with a number.
  They may have an optional ``$`` prefix.
  Variables with and without prefix are equivalent.
  Also a variable can share its name with a function without collision.

Example
=======

``'sin(x) * (coef1 + coef2)'``
