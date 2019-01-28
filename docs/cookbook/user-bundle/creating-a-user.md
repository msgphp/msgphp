# How to create an user?

Users can be created by dispatching `CreateUserCommand` message with a data array that contains the arguments required by `User` constructor.

For example, if you decided to use the `EmailPasswordCredential` trait the user email and password are the required arguments:

```php
/**
 * @ORM\Entity()
 */
class User extends BaseUser implements DomainEventHandlerInterface
{
    [...]
    use EmailPasswordCredential;
    
    [...]
    
    public function __construct(UserIdInterface $id, string $email, string $password)
    {
        $this->id           = $id;
        $this->credential   = new EmailPassword($email, $password);
    }
```

Create `User` by dispatching the message:

```php

    public function createUser(
        MessageBusInterface $bus
        [...]
    ): Response {
        [...]

        $bus->dispatch(new CreateUserCommand($data));
        
        [...]
        return new Response('Success!');
    }

```

Where `$data' should contain:

```php
    $data = [
        'email' => 'bar@foo.com',
        'password' => 'supersecret'
    ]
```