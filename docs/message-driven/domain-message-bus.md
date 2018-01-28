# Domain message bus

A domain message bus is bound to `MsgPhp\Domain\Message\DomainMessageBusInterface`. Its purpose is to dispatch any type
of message object and helps you to use [CQRS](cqrs.md) and [event sourcing](../event-sourcing/domain-event-handlers.md).

## Implementations

- `MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus`
    - Requires `simple-bus/message-bus`

## API

### `dispatch(object $message): mixed`

Dispatches the given message object. The bus can return a value coming from handlers, but is not required to do so.
