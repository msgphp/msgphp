# Code conventions

A brief description of code conventions this project follows.

## General principles

- No [SOLID](https://en.wikipedia.org/wiki/SOLID_(object-oriented_design)) violations, yet be pragmatic
- Reduce [lines of code](https://en.wikipedia.org/wiki/Source_lines_of_code) where possible
- Reduce coupling ([LoD](https://en.wikipedia.org/wiki/Law_of_Demeter))
- Favor latest stable PHP7 features
- Checks must pass (code style, static analysis & unit tests)
- Add PHPDoc / comments if needed for clarification or static analysis

## Code style

- Follows PSR2 and Symfony style
- `use` statements are declared in alpha-order
- `use` statements for `MsgPhp\` namespace are grouped by deepest common namespace

```php
<?php

// wrong
use MsgPhp\SomeB;
use MsgPhp\SomeA;
use MsgPhp\Some\SomeC;
use Other\SomeOtherB;
use Other\Some\SomeOtherC;
use Other\SomeOtherA;

// right
use MsgPhp\Some\SomeC;
use MsgPhp\{SomeA, SomeB};
use Other\Some\SomeOtherC;
use Other\SomeOtherA;
use Other\SomeOtherB;
```

## Static analysis

- Follows PHPStan level max
- Exclude / ignore rules are discussed per case/topic

## Unit tests

- All of the above, _in general_, applies to unit tests as well
