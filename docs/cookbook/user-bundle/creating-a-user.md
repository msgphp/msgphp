# Creating a User

Users can be created by dispatching the `CreateUserCommand` message with a data array that contains values for the `User`
constructor arguments.

```php
<?php

use MsgPhp\User\Command\CreateUserCommand;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUserCommand([]));
```

The handler will automatically add an `id` element to the data array holding an instance of `MsgPhp\User\UserIdInterface`.
Alternatively it can be passed upfront:

```php
<?php

use MsgPhp\User\UserId;
use MsgPhp\User\Command\CreateUserCommand;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUserCommand([
    'id' => new UserId(),
]));
```

To programmatically factorize an [identifier](../../ddd/identifiers.md), use the [object factory](../../ddd/object-factory.md):

```php
<?php

use MsgPhp\Domain\Factory\DomainObjectFactoryInterface;
use MsgPhp\User\UserIdInterface;
use MsgPhp\User\Command\CreateUserCommand;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var DomainObjectFactoryInterface $factory */
/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUserCommand([
    'id' => $factory->create(UserIdInterface::class),
]));
```

## Adding Custom Fields

Define the custom fields:

```php
<?php

// src/Entity/User/User.php

// ...

class User extends BaseUser
{
    // ...
    
    private $requiredField;
    private $optionalField;
    
    public function __construct(UserIdInterface $id, $requiredField, $optionalField = null)
    {
        $this->id = $id;
        $this->requiredField = $requiredField;
        $this->optionalField = $optionalField;
    }
    
    // ...
}
```

Specify the fields during dispatch:

```php
<?php

use MsgPhp\User\Command\CreateUserCommand;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUserCommand([
    'requiredField' => 'value',
]));

// alternatively:

$bus->dispatch(new CreateUserCommand([
    'requiredField' => 'value',
    'optionalField' => 'value',
]));
```
