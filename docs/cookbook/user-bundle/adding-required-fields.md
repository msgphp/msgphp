# How to add required fields during the user creation?

Sometimes you may want to pass some extra values or objects to `User` during its creation.

Lets say that you want the newly created user to belong to some `Company`. First of all `Company` should be added to  `User` constructor arguments.

```php
/**
 * @ORM\Entity()
 */
class User extends BaseUser implements DomainEventHandlerInterface
{
    [...]
    
    /** @var Company */
    private $company;
    
    public function __construct(UserIdInterface $id, string $email, string $password, Company $company)
    {
        $this->id = $id;
        $this->credential = new EmailPassword($email, $password);
        $this->company = $company;
    }
    [...]
}
```

Since the `$data` array sent with the `CreateUserCommand` message is being mapped to `User` constructor arguments `Company` should be added to the array before the message is dispatched.


```php

    [...]
    
    /* get Company if not in $data already */
    $data['company'] = ...->getRepository(Company::class)->findOneByName('ACME Inc.');

    $bus->dispatch(new CreateUserCommand($data));

```

