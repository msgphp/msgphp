# Message Bus

A domain message bus is bound to `MsgPhp\Domain\Message\DomainMessageBusInterface`. Its purpose is to dispatch any type
of message object and helps you to use [CQRS](cqrs.md) and [event sourcing](../event-sourcing/event-handlers.md).

!!! note
    See the [reference](../reference/messages.md) page for all available messages provided per domain

## API

### `dispatch(object $message): void`

Dispatches the given message object.

## Implementations

### `MsgPhp\Domain\Infra\Messenger\DomainMessageBus`

A Symfony Messenger tailored domain message bus.

- [Read more](../infrastructure/symfony-messenger.md#domain-message-bus)

### `MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus`

A SimpleBus tailored domain message bus.

- [Read more](../infrastructure/simple-bus.md#domain-message-bus)
