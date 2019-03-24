# Creating a User

Users can be created by dispatching the `CreateUser` message with a data array that contains values for the `User`
constructor arguments.

```php
<?php

use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUser([]));
```

The handler will automatically add an `id` element to the data array holding an instance of `MsgPhp\User\UserId`.
Alternatively it can be passed upfront:

```php
<?php

use MsgPhp\User\ScalarUserId;
use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUser([
    'id' => new ScalarUserId(),
]));
```

To programmatically factorize an [identifier](../../ddd/identifiers.md), use the [object factory](../../ddd/object-factory.md):

```php
<?php

use MsgPhp\Domain\Factory\DomainObjectFactory;
use MsgPhp\User\UserId;
use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var DomainObjectFactory $factory */
/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUser([
    'id' => $factory->create(UserId::class),
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
    
    public function __construct(UserId $id, $requiredField, $optionalField = null)
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

use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Messenger\MessageBusInterface;

/** @var MessageBusInterface $bus */
$bus->dispatch(new CreateUser([
    'requiredField' => 'value',
]));

// alternatively:

$bus->dispatch(new CreateUser([
    'requiredField' => 'value',
    'optionalField' => 'value',
]));
```
