# Changelog

## 1.x

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
