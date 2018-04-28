# Message Bus

A domain message bus is bound to `MsgPhp\Domain\Message\DomainMessageBusInterface`. Its purpose is to dispatch any type
of message object and helps you to use [CQRS](cqrs.md) and [event sourcing](../event-sourcing/event-handlers.md).

## API

### `dispatch(object $message): mixed`

Dispatches the given message object. The bus can return a value coming from handlers, but is not required to do so.

## Implementations

### `MsgPhp\Domain\Infra\Messenger\DomainMessageBus`

A Symfony Messenger tailored domain message bus.

- [Read more](../infrastructure/symfony-messenger.md#domain-message-bus)

### `MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus`

A SimpleBus tailored domain message bus.

- [Read more](../infrastructure/simple-bus.md#domain-message-bus)
