Upgrade Notes
#############

2.x to 3.0
==========

* ``addFunctions()`` no longer accepts callables, use ``addFunctionsFromCallables()``

  * ``addFunctionsFromCallables()`` has ``$lazy`` parameter that may overshadow a function called ``lazy()`` if you defined one
* CalcCallException is no longer the only exception that Calculator throws:

    .. code-block:: php

        <?php

        use Arokettu\ArithmeticParser\Calculator;
        use Arokettu\ArithmeticParser\Exceptions\CalcCallException;
        use Arokettu\ArithmeticParser\Exceptions\CalcConfigException;
        use Arokettu\ArithmeticParser\Exceptions\CalculatorException;

        // 2.x:

        try {
            Calculator::evaluate('...', ...);
        } catch (CalcCallException $e) {
            // everything here except for ArgumentCountError and TypeError
        }

        // 3.0:

        try {
            Calculator::evaluate('...', ...);
        } catch (CalcCallException $e) {
            // runtime exceptions (undefined funcs and undefined vars)
        } catch (CalcConfigException $e) {
            // generally parser errors and invalid variable lists
        } catch (CalculatorException $e) {
            // catch-all like CalcCallException was in 2.x
        }

1.x to 2.0
==========

* Comma (``,``) can no longer be used as an operator
* ``InvalidArgumentException`` was replaced with ``DomainException``
* Absolute values of ``Config\BinaryOperator::PRIORITY_*`` constants changed
