# Entities

Reference of available [entities](../ddd/entities.md) per domain.

<!--ref-start:entities-->
## `msgphp/domain`

### Fields

- `MsgPhp\Domain\Entity\Fields\CreatedAtField`
- `MsgPhp\Domain\Entity\Fields\EnabledField`
- `MsgPhp\Domain\Entity\Fields\LastUpdatedAtField`

### Features

- `MsgPhp\Domain\Entity\Features\CanBeConfirmed`
- `MsgPhp\Domain\Entity\Features\CanBeEnabled`

## `msgphp/eav`

### Entities

Class | Abstract
--- | ---
`MsgPhp\Eav\Entity\Attribute` | ✔
`MsgPhp\Eav\Entity\AttributeValue` | ✔

### Fields

- `MsgPhp\Eav\Entity\Fields\AttributesField`

### Features

- `MsgPhp\Eav\Entity\Features\EntityAttributeValue`

## `msgphp/user`

### Entities

Class | Abstract
--- | ---
`MsgPhp\User\Entity\Role` | ✔
`MsgPhp\User\Entity\User` | ✔
`MsgPhp\User\Entity\UserAttributeValue` | ✔
`MsgPhp\User\Entity\UserEmail` | ✔
`MsgPhp\User\Entity\UserRole` | ✔
`MsgPhp\User\Entity\Username` | ✗

### Fields

- `MsgPhp\User\Entity\Fields\AttributeValuesField`
- `MsgPhp\User\Entity\Fields\EmailsField`
- `MsgPhp\User\Entity\Fields\RoleField`
- `MsgPhp\User\Entity\Fields\RolesField`
- `MsgPhp\User\Entity\Fields\UserField`

### Features

- `MsgPhp\User\Entity\Features\EmailCredential`
- `MsgPhp\User\Entity\Features\EmailPasswordCredential`
- `MsgPhp\User\Entity\Features\EmailSaltedPasswordCredential`
- `MsgPhp\User\Entity\Features\NicknameCredential`
- `MsgPhp\User\Entity\Features\NicknamePasswordCredential`
- `MsgPhp\User\Entity\Features\NicknameSaltedPasswordCredential`
- `MsgPhp\User\Entity\Features\ResettablePassword`
- `MsgPhp\User\Entity\Features\TokenCredential`

<!--ref-end:entities-->
