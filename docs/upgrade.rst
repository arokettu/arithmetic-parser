Upgrade Notes
#############

2.x to 3.0
==========

* ``setFunctions()`` and ``addFunctions()`` no longer accept callables, use ``addFunctionsFromCallables()``

  * ``addFunctionsFromCallables()`` has ``$lazy`` parameter that may overshadow a function called ``lazy()`` if you defined one

  .. code-block:: php

        <?php

        use Arokettu\ArithmeticParser\Config;

        $config = Config::default();

        // 2.x:

        $config->addFunctions(
            myfunc2: fn ($a) => $a ** 2,
            myfunc3: fn ($a) => $a ** 3,
        );

        // 3.0:

        $config->addFunctionsFromCallables(
            myfunc2: fn ($a) => $a ** 2,
            myfunc3: fn ($a) => $a ** 3,
        );

        // 2.x:

        $config->setFunctions(
            myfunc2: fn ($a) => $a ** 2,
            myfunc3: fn ($a) => $a ** 3,
        );

        // 3.x:

        $config->clearFunctions();
        $config->addFunctionsFromCallables(
            myfunc2: fn ($a) => $a ** 2,
            myfunc3: fn ($a) => $a ** 3,
        );

* ``CalcCallException``` is no longer the only exception that Calculator throws:

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
