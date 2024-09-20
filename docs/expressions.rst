Expressions
###########

Supported Elements
==================

.. note:: Operators and functions may support lazy calculation, see :ref:`lazy-calc`

Supported expressions can include:

* Unary operators ``+`` and ``-`` with any custom operators added.
* Binary ``+``, ``-``,
  ``*``, ``/``,
  ``<``, ``>``, ``<=``, ``>=``,
  ``=`` (also ``==``), ``<>`` (also ``!=``),
  ``and`` (also ``AND``), ``or`` (also ``OR``),
  ``not`` (also ``NOT``)
  with any custom operators added.
  All operators except for ``+`` and ``-`` can be disabled.

  * ``and/AND``, ``or/OR`` operators are lazy, see :ref:`lazy-calc`.
* Numbers, obviously.
  All numbers are cast to float internally.
* Functions.
  Function names are case insensitive alphanumeric strings that do not start with a number.
  They may have an optional ``@`` prefix.
  Functions with and without prefix are equivalent.
  Functions accept a single parameter.
  Custom functions can be created with a Config object.
  See :ref:`calc-config-funcs` for a full list.
* Variables.
  Variable names are case insensitive alphanumeric strings that do not start with a number.
  They may have an optional ``$`` prefix.
  Variables with and without prefix are equivalent.
  Also a variable can share its name with a function without collision.

Example
=======

``sin(x) * (coef1 + coef2)``
