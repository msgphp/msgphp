# Message Bus

A domain message bus is bound to `MsgPhp\Domain\Message\DomainMessageBus`. Its purpose is to dispatch any type of
message object and helps you to use [CQRS](cqrs.md) and [event sourcing](../event-sourcing/event-handlers.md).

## API

### `dispatch(object $message): void`

Dispatches the given message object.

## Implementations

### `MsgPhp\Domain\Infrastructure\Messenger\DomainMessageBus`

A Symfony Messenger tailored domain message bus.

- [Read more](../infrastructure/symfony-messenger.md#domain-message-bus)
