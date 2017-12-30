# MsgPhp

Main development repository.

[![Build Status](https://travis-ci.org/msgphp/msgphp.svg?branch=master)](https://travis-ci.org/msgphp/msgphp) [![codecov](https://codecov.io/gh/msgphp/msgphp/branch/master/graph/badge.svg)](https://codecov.io/gh/msgphp/msgphp)

## Packages

- [`msgphp/domain`](https://github.com/msgphp/domain)
- [`msgphp/eav`](https://github.com/msgphp/eav)
- [`msgphp/user`](https://github.com/msgphp/user)

## Bundles

- [`msgphp/eav-bundle`](https://github.com/msgphp/eav-bundle)
- [`msgphp/user-bundle`](https://github.com/msgphp/user-bundle)

## Testing

Tests can be run via the helper script in `bin/phpunit`

```bash
bin/phpunit
```

To first install dependencies:

```bash
bin/composer update
```

Each of these scripts will iterate through the subtrees in `src/` and call the
respective binary, passing all options, e.g.

```bash
bin/phpunit --coverage-text
```
