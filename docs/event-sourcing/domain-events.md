# Domain events

A domain event is bound to `MsgPhp\Domain\Event\DomainEventInterface`. Its purpose is to identify concrete domain events
and represent something that happens. When handled it might lead to an application state change.

## Implementations

Domain events provided and handled by default [entity features](../ddd/entities.md):

- `MsgPhp\Domain\Event\ConfirmEvent`
- `MsgPhp\Domain\Event\DisableEvent`
- `MsgPhp\Domain\Event\EnableEvent`
