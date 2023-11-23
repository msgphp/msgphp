# NOT ACTIVELY SUPPORTED ANY MORE!!

msgphp/* repositories are not actively developed/supported anymore.

**Use in production on your own risks.**

If you want to do some hotfixes - please do PR directly in target repository instead of this monorepository.

# Message Driven PHP

[![Build Status][master:travis:img]][master:travis]
[![Code Coverage][master:codecov:img]][master:codecov]

MsgPHP is a project that aims to provide reusable domain layers for your application. It has a low development time
overhead and avoids being overly opinionated.

It follows [Semantic Versioning], yet during development phase a package can be marked `@experimental` to indicate
"[BC] breaks" could be introduced.

## Domain Layers

> The domain layer is a collection of entity objects and related business logic that is designed to represent the
enterprise business model. The major scope of this layer is to create a standardized and federated set of objects, that
could be potentially reused within different projects. ([source](https://www.javacodegeeks.com/2013/05/multilayered-architecture-2-the-domain-layer.html))

Currently supported domain layers are:

- [`Eav`][domain:eav]
- [`User`][domain:user]
  - [`UserEav`][domain:user-eav]

On the roadmap are:

- `Organization`
- `File`
- `Taxonomy`
- ...

## Design-Time Considerations

- The base domain package (`msgphp/domain`) integrates with **YOUR** domain layer (it's dependency free by design)
- You inherit from the default domain layers, if used
- The first-class supported ORM is [Doctrine ORM]
- The first-class supported message bus is [Symfony Messenger]

## Message Based

Each domain layer provides a set of messages to consume it. Typically the messages are categorized into command-, event-
and query-messages.

The main advantage is we can intuitively create an independent flow of business logic. It provides consistency in
handling our business logic by dispatching the same message in e.g. web as well as CLI.

```php
$anyMessageBus->dispatch(new CreateSomething(['field' => 'value']));
```

A command-message is dispatched and picked up by its handler to do the work. This handler on itself dispatches a new
event-message (e.g. `SomethingCreated`) to notify the work is done.

A custom handler can subscribe to the `SomethingCreated` event to further finalize the business requirements (e.g.
dispatch another commmand-message).

```php
class MakeSomethingWork
{
    private $bus;

    public function __construct(YourFavoriteBus $bus)
    {
        $this->bus = $bus;
    }

    public function __invoke(SomethingCreated $event)
    {
        // do work, or better delegate work:
        $this->bus->dispatch(new LetSomethingWork($event->something));
    }
}
```

# Documentation

- Read the [main documentation](https://msgphp.github.io/docs/)
- Try the Symfony [demo application](https://github.com/msgphp/symfony-demo-app)
- Get support on [Symfony's Slack `#msgphp` channel](https://symfony.com/slack-invite) or [raise an issue](https://github.com/msgphp/msgphp/issues/new)

# Contributing

See [`CONTRIBUTING.md`](CONTRIBUTING.md)

[master:travis]: https://travis-ci.com/msgphp/msgphp
[master:travis:img]: https://img.shields.io/travis/com/msgphp/msgphp/master.svg?style=flat-square
[master:codecov]: https://codecov.io/gh/msgphp/msgphp
[master:codecov:img]: https://img.shields.io/codecov/c/github/msgphp/msgphp/master.svg?style=flat-square
[domain:eav]: https://github.com/msgphp/eav
[domain:user]: https://github.com/msgphp/user
[domain:user-eav]: https://github.com/msgphp/user-eav
[Semantic Versioning]: https://semver.org/
[BC]: https://en.wikipedia.org/wiki/Backward_compatibility
[Doctrine ORM]: https://www.doctrine-project.org/projects/orm.html
[Symfony Messenger]: https://symfony.com/doc/current/components/messenger.html
