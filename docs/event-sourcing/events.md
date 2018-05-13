# Events

A domain event is bound to `MsgPhp\Domain\Event\DomainEventInterface`. Its purpose is to represent any action that can
 _happen_ regarding the domain. When handled it might lead to an application state change.

!!! note
    See the [reference](../reference/domain-events.md) page for all available events provided per domain

## Implementations

### `MsgPhp\Domain\Event\ConfirmEvent`

Triggers a confirmation. Handled by default with `MsgPhp\Domain\Entity\Features\CanBeConfirmed::handleConfirmEvent()`.

### `MsgPhp\Domain\Event\DisableEvent`

Triggers disabling availability. Handled by default with `MsgPhp\Domain\Entity\Features\CanBeEnabled::handleDisableEvent()`.

### `MsgPhp\Domain\Event\EnableEvent`

Triggers enabling availability. Handled by default with `MsgPhp\Domain\Entity\Features\CanBeEnabled::handleEnableEvent()`.
