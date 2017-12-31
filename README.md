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

## Contributing

### Setup environment

Run the following command to setup your environment first:

```bash
bin/setup
```

### Testing

Run tests for all components:

```bash
bin/phpunit
```

Run tests for a single component, e.g. `Domain`:

```bash
cd src/Domain/
vendor/bin/simple-phpunit
```

### Code style

Analyze all components:

```bash
bin/cs
```

Analyze a single component, e.g. `Domain`:

```bash
bin/cs src/Domain/
```

### Static analysis

Analyze all components:

```bash
bin/sa
```

Analyze a single component, e.g. `Domain`:

```bash
bin/sa src/Domain/
```
