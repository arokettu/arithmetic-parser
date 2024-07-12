Validator
#########

.. highlight:: php

``Arokettu\ArithmeticParser\Validator`` can be used to catch situations that are valid for the parser
but will definitely fail in calculator, namely:

* Undefined variable. A list of available variables is unknown during parsing time
* Undefined function. A list of functions may be not final during parsing time,
  this allows users to have dynamic functions if needed
* Not enough arguments passed to a function. Same reasons, function list may be dynamic

``Validator\Warning``
=====================

``Arokettu\ArithmeticParser\Validator\Warning``

An object describing a validator warning.
There are 3 warnings currently:

* ``MissingVariablesWarning``
* ``MissingFunctionsWarning``
* ``MissingFunctionArgumentsWarning``

::

    <?php

    use Arokettu\ArithmeticParser\Validator;

    $warnings = Validator::validate(/* ... */);

    foreach ($warnings as $warning) {
        // check which warning is it
        $funcsMissing = $warning instanceof Validator\MissingFunctionsWarning;

        // get message describing the warning
        $errorMessage = $warning->getMessage();

        // convert to an exception
        throw $warning->toException();
    }

Methods
=======

Validator exposes several methods that do a single thing but tailored to different possible use semantics

``Validator::validate()``
-------------------------

Returns an array of ``Arokettu\ArithmeticParser\Validator\Warning``::

    <?php

    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Parser;
    use Arokettu\ArithmeticParser\Validator;

    $config = Config::default();
    $parsed = (new Parser($config))->parse('a + 1');

    $warnings = Validator::validate($parsed, $config, ['a']);
    $isValid = $warnings === [];

    foreach ($warnings as $warning) {
        // ...
    }

``Validator::isValid()``
------------------------

Returns a boolean, can optionally populate a list of warnings::

    <?php

    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Parser;
    use Arokettu\ArithmeticParser\Validator;

    $config = Config::default();
    $parsed = (new Parser($config))->parse('a + 1');

    $isValid = Validator::isValid($parsed, $config, ['a'], $warnings);

    foreach ($warnings as $warning) {
        // ...
    }

``Validator::assertValid()``
----------------------------

Throws an exception when the input is not valid::

    <?php

    declare(strict_types=1);

    require __DIR__ . '/../vendor/autoload.php';

    use Arokettu\ArithmeticParser\Config;
    use Arokettu\ArithmeticParser\Exceptions\ValidationException;
    use Arokettu\ArithmeticParser\Parser;
    use Arokettu\ArithmeticParser\Validator;

    $config = Config::default();
    $parsed = (new Parser($config))->parse('a + 1');

    try {
        Validator::assertValid($parsed, $config, ['a']);
        $isValid = true;
    } catch (ValidationException $e) {
        $isValid = false;
    }
