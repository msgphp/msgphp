<?php

namespace App\Controller\User;

use App\Form\User\ForgotPasswordType;
use MsgPhp\User\Command\RequestUserPasswordCommand;
use MsgPhp\User\Repository\UserRepositoryInterface;
use SimpleBus\SymfonyBridge\Bus\CommandBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class ForgotPasswordController
{
    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        CommandBus $bus,
        UserRepositoryInterface $repository
    ): Response
    {
        $form = $formFactory->createNamed('', ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bus->handle(new RequestUserPasswordCommand($repository->findByUsername($email = $form->getData()['email'])->getId()));
            $flashBag->add('success', sprintf('Hi %s, we\'ve send you a password reset link.', $email));

            return new RedirectResponse($urlGenerator->generate('index'));
        }

        return new Response($twig->render('user/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
