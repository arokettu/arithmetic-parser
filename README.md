# Arithmetic Parser for PHP

[![Packagist](https://img.shields.io/packagist/v/arokettu/arithmetic-parser.svg?style=flat-square)](https://packagist.org/packages/arokettu/arithmetic-parser)
[![PHP](https://img.shields.io/packagist/php-v/arokettu/arithmetic-parser.svg?style=flat-square)](https://packagist.org/packages/arokettu/arithmetic-parser)
[![Packagist](https://img.shields.io/github/license/arokettu/arithmetic-parser.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Gitlab pipeline status](https://img.shields.io/gitlab/pipeline/sandfox/arithmetic-parser/master.svg?style=flat-square)](https://gitlab.com/sandfox/arithmetic-parser/-/pipelines)
[![Codecov](https://img.shields.io/codecov/c/gl/sandfox/arithmetic-parser?style=flat-square)](https://codecov.io/gl/sandfox/arithmetic-parser/)

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

TODO

## Support

Please file issues on our main repo at GitLab: <https://gitlab.com/sandfox/arithmetic-parser/-/issues>

## License

The library is available as open source under the terms of the [MIT License].

[MIT License]:  https://opensource.org/licenses/MIT
