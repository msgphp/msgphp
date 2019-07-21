# Message Bus

A domain message bus is bound to `MsgPhp\Domain\DomainMessageBus`. Its purpose is to dispatch any type of message object
either synchronously or asynchronously.

A message object is considered [domain-specific language] (DSL) and can be listened for using so called "message
handlers" (a [PHP callable]).

## Command Query Responsibility Segregation

Commands are a specific type of messages, typically used for "commanding" the domain, thus describing actions to be
taken.

Typically commands require exactly one "command handler" listening.

## API

### `dispatch(object $message): void`

Dispatches the given message object.

## Implementations

### `MsgPhp\Domain\Infrastructure\Messenger\DomainMessageBus`

A Symfony Messenger tailored domain message bus.

- [Read more](../infrastructure/symfony-messenger.md#domain-message-bus)

[domain-specific language]: https://en.wikipedia.org/wiki/Domain-specific_languages
[PHP callable]: https://www.php.net/manual/en/language.types.callable.php
