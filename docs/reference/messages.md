# Messages

Reference of available messages per domain that can be dispatched using a [message bus](../message-driven/message-bus.md).

<!--ref-start:messages-->
## `msgphp/domain`

### Commands

- `MsgPhp\Domain\Command\DeleteProjectionDocumentCommand`
- `MsgPhp\Domain\Command\SaveProjectionDocumentCommand`

### Events

- `MsgPhp\Domain\Event\ProjectionDocumentDeletedEvent`
- `MsgPhp\Domain\Event\ProjectionDocumentSavedEvent`

## `msgphp/eav`

### Commands

- `MsgPhp\Eav\Command\CreateAttributeCommand`
- `MsgPhp\Eav\Command\DeleteAttributeCommand`

### Events

- `MsgPhp\Eav\Event\AttributeCreatedEvent`
- `MsgPhp\Eav\Event\AttributeDeletedEvent`

## `msgphp/user`

### Commands

- `MsgPhp\User\Command\AddUserAttributeValueCommand`
- `MsgPhp\User\Command\AddUserEmailCommand`
- `MsgPhp\User\Command\AddUserRoleCommand`
- `MsgPhp\User\Command\ChangeUserAttributeValueCommand`
- `MsgPhp\User\Command\ChangeUserCredentialCommand`
- `MsgPhp\User\Command\ConfirmUserCommand`
- `MsgPhp\User\Command\ConfirmUserEmailCommand`
- `MsgPhp\User\Command\CreateRoleCommand`
- `MsgPhp\User\Command\CreateUserCommand`
- `MsgPhp\User\Command\DeleteRoleCommand`
- `MsgPhp\User\Command\DeleteUserAttributeValueCommand`
- `MsgPhp\User\Command\DeleteUserCommand`
- `MsgPhp\User\Command\DeleteUserEmailCommand`
- `MsgPhp\User\Command\DeleteUserRoleCommand`
- `MsgPhp\User\Command\DisableUserCommand`
- `MsgPhp\User\Command\EnableUserCommand`
- `MsgPhp\User\Command\RequestUserPasswordCommand`

### Events

- `MsgPhp\User\Event\RoleCreatedEvent`
- `MsgPhp\User\Event\RoleDeletedEvent`
- `MsgPhp\User\Event\UserAttributeValueAddedEvent`
- `MsgPhp\User\Event\UserAttributeValueChangedEvent`
- `MsgPhp\User\Event\UserAttributeValueDeletedEvent`
- `MsgPhp\User\Event\UserConfirmedEvent`
- `MsgPhp\User\Event\UserCreatedEvent`
- `MsgPhp\User\Event\UserCredentialChangedEvent`
- `MsgPhp\User\Event\UserDeletedEvent`
- `MsgPhp\User\Event\UserDisabledEvent`
- `MsgPhp\User\Event\UserEmailAddedEvent`
- `MsgPhp\User\Event\UserEmailConfirmedEvent`
- `MsgPhp\User\Event\UserEmailDeletedEvent`
- `MsgPhp\User\Event\UserEnabledEvent`
- `MsgPhp\User\Event\UserPasswordRequestedEvent`
- `MsgPhp\User\Event\UserRoleAddedEvent`
- `MsgPhp\User\Event\UserRoleDeletedEvent`

<!--ref-end:messages-->
