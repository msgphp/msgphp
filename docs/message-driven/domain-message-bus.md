# Domain message bus

A domain message bus is bound to `MsgPhp\Domain\DomainMessageBusInterface`. Its purpose is to dispatch any type of
message object and helps you to use CQRS and event sourcing.

## Implementations

- `MsgPhp\Domain\Infra\SimpleBus\DomainMessageBus`
    - Dispatches both _command_ and _event_ message types
    - Requires `simple-bus/message-bus`

## API

### `dispatch(object $message): mixed`

Dispatches the given message object. The bus can return a value coming from handlers, but is not required to do so.
