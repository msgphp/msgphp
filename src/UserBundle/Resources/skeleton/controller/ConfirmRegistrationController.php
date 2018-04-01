<?php

namespace App\Controller\User;

use App\Entity\User\User;
use MsgPhp\User\Command\ConfirmUserCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\SymfonyBridge\Bus\CommandBus;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ConfirmRegistrationController
{
    /**
     * @ParamConverter("user", converter="doctrine.orm", options={"mapping": {"token": "confirmationToken"}})
     */
    public function __invoke(
        User $user,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator,
        CommandBus $bus
    ): Response
    {
        $bus->handle(new ConfirmUserCommand($user->getId()));
        $flashBag->add('success', sprintf('Hi %s, your registration is confirmed. You can now login.', $user->getCredential()->getUsername()));

        return new RedirectResponse($urlGenerator->generate('login'));
    }
}
