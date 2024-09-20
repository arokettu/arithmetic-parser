Parser
######

.. highlight:: php

``Arokettu\ArithmeticParser\Parser`` class is used to parse an arithmetic expression
to the `Reverse Polish notation`_ by the `shunting yard algorithm`_.

.. _Reverse Polish notation: https://en.wikipedia.org/wiki/Reverse_Polish_notation
.. _shunting yard algorithm: https://en.wikipedia.org/wiki/Shunting_yard_algorithm

Parser API is not considered stable.

``parse()``
===========

``parse`` method returns an array of operation objects for RPN stack machine.
Example::

    <?php

    use Arokettu\ArithmeticParser\Operation\BinaryOperator;
    use Arokettu\ArithmeticParser\Operation\Number;
    use Arokettu\ArithmeticParser\Operation\Variable;
    use Arokettu\ArithmeticParser\Parser;

    $parser = new Parser();

    $rpn = $parser->parse('x + 3 * y'); // rpn is x 3 y * +

    $rpn->operations == [
        new Variable('x'),
        new Number(3),
        new Variable('y'),
        new BinaryOperator('*'),
        new BinaryOperator('+'),
    ]; // true

    var_dump($rpn->asString()); // "x 3 y * +"
