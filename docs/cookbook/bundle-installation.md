# Bundle Installation

The project bundles are tailored to the [Symfony Framework] and designed to be used standalone. Its main purpose is to
provide the application with services, based on minimal configuration and to be used with [dependency injection]. In
general [autowiring] is leveraged in an effort to favor convention over configuration where possible.

Various services might be created depending on other enabled bundles. If a supported bundle is enabled its available
infrastructural services are created automatically.

## Available bundles

<!--ref-start:available-bundles-->
- `msgphp/eav-bundle`: Basic entity-attribute-value management (the `Eav` domain)
- `msgphp/user-bundle`: Basic user management (the `User` domain)
<!--ref-end:available-bundles-->

## Installation

```bash
composer require msgphp/<name>-bundle
```

### With Symfony Flex

When [Symfony Flex] is used to mange your Symfony application the minimal bundle configuration is created for you
automatically based on [MsgPHP recipes].

## Configuration

By default a bundle provides the following minimal configuration:

```yaml
# config/packages/msgphp_<name>.yaml

msgphp_<name>:
    class_mapping: []
    id_type_mapping: []
    default_id_type: integer
    commands: []
```

Depending on your personal preference you can also write the configuration in any other supported format. See the
[demo configuration] for a more advanced example.

### `class_mapping`

Configures the bundle with a class mapping to to well which classes of yours should be used for a known class of ours.

```yaml
msgphp_<name>:
    class_mapping:
        MsgPhp\SomeClass: App\SomeClass
```

The class mapping applies when working with an [object factory](../ddd/factory/object.md#).

Depending on the bundle a specific class mapping entry might enable one of the bundle its features which is otherwise
disabled by default.

### `id_type_mapping`

Configures the bundle [domain identifier](../ddd/identifiers.md) types. Each key must be a sub class of
`MsgPhp\Domain\DomainIdInterface` whereas each value must be a known type name.

It ensures a default class mapping entry is added which maps the identifier to a [concrete implementation](../ddd/identifiers.md#implementations).

```yaml
msgphp_<name>:
    id_type_mapping:
        MsgPhp\SomeDomainIdInterface: some_type_name
```

By convention any [Doctrine DBAL type] can be used. Additionally the following UUID types are detected as well:

- `uuid`
- `uuid_binary`
- `uuid_binary_ordered_time`

See also the [reference](../reference/identifiers.md) page for all available identifiers provided per domain.

### `default_id_type`

Configures a default type name to use for all known domain identifiers provided by the bundle.

```yaml
msgphp_<name>:
    default_id_type: uuid
```

### `commands`

By default a command handler provided by the bundle might be enabled or disabled depending on an [entity feature](../ddd/entities.md#common-entity-features)
is being used yes or no.

However, in case of a [event-sourcing command handler](../message-driven/cqrs.md#event-sourcing-command-handler)
the corresponding [domain event](../event-sourcing/events.md) might be supported regardless. Depending on your own
[event handler](../event-sourcing/event-handlers.md) implementation. To keep leveraging default command handlers they
can be explicitly enabled or disabled by command.

```yaml
msgphp_<name>:
    commands:
        MsgPhp\SomeCommand: true
        MsgPhp\SomeOtherCommand: false
```

## Basic configuration example

Given a bundle provides the following domain identifiers:

- `MsgPhp\FooIdInterface`
- `MsgPhp\BarIdInterface`
- `MsgPhp\BazIdInterface`

```yaml
msgphp_<name>:
    default_id_type: uuid
    id_type_mapping:
        MsgPhp\FooIdInterface: integer
        # implied:
        # MsgPhp\BarIdInterface: uuid
        # MsgPhp\BazIdInterface: uuid
    class_mapping:
        MsgPhp\BarIdInterface: App\MyBarUuid
        # implied:
        # MsgPhp\FooIdInterface: MsgPhp\Domain\DomainId
        # MsgPhp\BazIdInterface: MsgPhp\Domain\Infra\Uuid\DomainId
```

[Symfony Framework]: https://symfony.com/
[dependency injection]: https://symfony.com/doc/current/components/dependency_injection.html
[Symfony Flex]: https://symfony.com/doc/current/setup/flex.html
[MsgPHP recipes]: https://github.com/symfony/recipes-contrib/tree/master/msgphp
[autowiring]: https://symfony.com/doc/current/service_container/autowiring.html
[demo configuration]: https://github.com/msgphp/symfony-demo-app/blob/master/config/packages/msgphp.php
[Doctrine DBAL type]: http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html
