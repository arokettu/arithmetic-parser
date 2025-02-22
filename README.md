# Arithmetic Parser for PHP

[![Packagist]][Packagist Link]
[![PHP]][Packagist Link]
[![License]][License Link]
[![Gitlab CI]][Gitlab CI Link]
[![Codecov]][Codecov Link]

[Packagist]: https://img.shields.io/packagist/v/arokettu/arithmetic-parser.svg?style=flat-square
[PHP]: https://img.shields.io/packagist/php-v/arokettu/arithmetic-parser.svg?style=flat-square
[License]: https://img.shields.io/github/license/arokettu/arithmetic-parser.svg?style=flat-square
[Gitlab CI]: https://img.shields.io/gitlab/pipeline/sandfox/arithmetic-parser/master.svg?style=flat-square
[Codecov]: https://img.shields.io/codecov/c/gl/sandfox/arithmetic-parser?style=flat-square

[Packagist Link]: https://packagist.org/packages/arokettu/arithmetic-parser
[License Link]: LICENSE.md
[Gitlab CI Link]: https://gitlab.com/sandfox/arithmetic-parser/-/pipelines
[Codecov Link]: https://codecov.io/gl/sandfox/arithmetic-parser/

A library that can parse and interpret arithmetic expressions.
It's aimed to be configurable and safe to process end user's input. 

## Installation

```bash
composer require 'arokettu/arithmetic-parser'
```

## Example

```php
<?php

\Arokettu\ArithmeticParser\Calculator::evaluate('x + y', x: 2, y: 3); // 5
```

## Documentation

Read the full documentation here: <https://sandfox.dev/php/arithmetic-parser.html>

Also on Read the Docs: <https://php-arithmetic-parser.readthedocs.io>

## Support

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/arithmetic-parser/-/issues>

Feel free to ask any questions in our room on Gitter: <https://gitter.im/arokettu/community>

## License

The library is available as open source under the terms of the [MIT License][License Link].
