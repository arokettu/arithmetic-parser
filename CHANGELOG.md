# Changelog

## 2.x

### 2.0.1

*Jun 21, 2024*

* Fixed `"1 * (+)"` being parsed successfully

### 2.0.0

*Jun 20, 2024*

Branched from 1.1.1

* Functions can accept any number of arguments
  * ``,`` is now reserved as an operator name
* New default functions:
  * `pi()`
  * `e()`
  * `if()`
  * `log()` now accepts base parameter
* New default operators:
  * `<`, `>`, `<=`, `>=`
  * `=` (`==`), `<>` (`!=`)
  * `and` (`AND`), `or` (`OR`)
* The library will catch more invalid situations than before
* ``Parser\Parsed::asString()`` - RPN notation for the parsed expression

## 1.x

### 1.1.1

*Jan 9, 2024*

* Remove dependency on php-ds because of slow polyfill performance

### 1.1.0

*Apr 10, 2023*

* Added semantic aliases for config methods:
  * `removeFunction('abc')` -> `removeFunctions(/* single argument */ 'abs')`
  * `removeOperator('/')` -> `removeOperators(/* single argument */ '/')`
  * `clearFunctions()` -> `setFunctions(/* empty list */)`
  * `clearOperators()` -> `setOperators(/* empty list */)`

### 1.0.0

*Mar 13, 2023*

* Missing operator is now detected on parsing stage, not on calculation.

## 0.x

### 0.4.0 "RC1"

*Feb 23, 2023*

* Parser now also returns lists of variables and functions that are used in the expression
* Custom binary operators
* Custom unary operators
* ConfigBuilder was removed 

### 0.3.0

*Feb 13, 2023*

* Parser should now catch all invalid operator sequences before calculation
* Parser now throws more narrow ParseException on invalid input instead of \RuntimeException
* Calculator now throws more narrow CalcCallException on incorrectly configured calculation instead of \InvalidArgumentException

### 0.2.0

*Feb 10, 2023*

* Config object
* ConfigBuilder object
* Calculator constructor signature changed
* Variables can be prefixed with $ optionally
* Custom functions
  * More functions added to the default configuration

### 0.1.0

*Feb 9, 2023*

Initial release
