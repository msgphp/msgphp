# How to...

## How to set extra user property during the user creation?

Sometimes you may want to pass some extra values or objects to the user when creating one.

For the sake of the example lets say that you want to set custom generated unique id (UID) for each user that is being created. In order to do this you will need to pass this UID to the User entity __construct function.

Example controller action where we create new user:

```php

    [...]

    /**
     * @Route("/user/new", name="user_new", methods="GET|POST")
     *
     * @ParamConverter("user", converter="msgphp.current_user")
     */
    public function newUser(
        Request $request,
        User $user,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        MessageBusInterface $bus
    ): Response
    {
        # note that $user in newUser refers to the logged-in user
    
        # some registration form
        $form = $formFactory->createNamed('', RegisterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $uidGenerator = new UidGenerator();

            $data['uid'] = $uidGenerator->getUid();

            $bus->dispatch(new CreateUserCommand($data));

            $flashBag->add('success', sprintf('New user was created: "%s".', $data['email']));

            return $this->redirectToRoute([...]);
        }

        return $this->render('[...]user/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
```
The UidGenerator class (which must be implemented by you) is used to generate the unique id which we add to the array of data returned by the registration form before forwarding it to CreateUserCommand:

    $bus->dispatch(new CreateUserCommand($data));

Next, add the new UID argument to the User entity constructor and set the uid property:

```php
/**
 * @ORM\Entity()
 */
class User extends BaseUser implements DomainEventHandlerInterface
{
    [...]
    public function __construct(UserIdInterface $id, string $email, string $password, string $uid)
    {
        $this->id = $id;
        $this->credential = new EmailPassword($email, $password);
        $this->uid = $uid;
    }
    [...]
}
```

That's it.