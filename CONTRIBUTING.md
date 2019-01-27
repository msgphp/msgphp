# Contributing

```bash
# run unit tests
make phpunit

# run code style checks
make cs

# run static analysis checks
make sa

# run all tests/checks on latest deps
make smoke-test
```

_([Docker] required)_

## Setup a Test Project

Create a new bare Symfony skeleton application with specific MsgPHP bundles pre-installed.

```bash
bin/create-project
```

[Docker]: https://www.docker.com/
