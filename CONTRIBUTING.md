# Contributing

## Testing

```bash
# all packages
bin/phpunit

# single package
bin/phpunit user-bundle
```

## Code Style

```bash
# all packages
bin/cs

# single package
bin/cs user-bundle
```

## Static Analysis

```bash
# all packages
bin/sa

# single package
bin/sa user-bundle
```

## Helping others

To checkout another open pull request from this repository use:

```bash
bin/pr <pr-number>
```

It will add a new git remote `github-pr-XXX` pointing to the author's SSH URL and checkout their branch locally using
the same name.

## Setup a test project

To setup a test project use:

```bash
bin/create-project
```

It will create a new Symfony skeleton application and ask you which bundles to install. The bundles can be automatically
symlinked to your local clone after.

## Perform a smoke test

To quickly see if CI is likely to pass use:

```bash
bin/smoke-test
bin/smoke-test-bundles
```

## Using [Lando](https://docs.devwithlando.io)

```bash
lando bash 'bin/<cmd> <arg>'
```
