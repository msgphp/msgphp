<?php

namespace App\Controller\User;

use App\Entity\User\User;
use App\Form\User\ResetPasswordType;
use MsgPhp\User\Command\ChangeUserCredentialCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SimpleBus\SymfonyBridge\Bus\CommandBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class ResetPasswordController
{
    /**
     * @ParamConverter("user", converter="doctrine.orm", options={"mapping": {"token": "passwordResetToken"}})
     */
    public function __invoke(
        User $user,
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        CommandBus $bus
    ): Response
    {
        $form = $formFactory->createNamed('', ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bus->handle(new ChangeUserCredentialCommand($user->getId(), ['password' => $form->getData()['password']]));
            $flashBag->add('success', sprintf('Hi %s, we\'ve reset your password.', $user->getEmail()));

            return new RedirectResponse($urlGenerator->generate('index'));
        }

        return new Response($twig->render('user/reset_password.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
