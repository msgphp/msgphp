<?php

namespace App\Controller\User;

use App\Form\User\RegisterType;
use MsgPhp\User\Command\CreateUserCommand;
use SimpleBus\SymfonyBridge\Bus\CommandBus;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class RegisterController
{
    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator,
        Environment $twig,
        CommandBus $bus
    ): Response
    {
        $form = $formFactory->createNamed('', RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bus->handle(new CreateUserCommand($data = $form->getData()));
            $flashBag->add('success', sprintf('Hi %s, you\'re successfully registered. We\'ve send you a confirmation link.', $data['email']));

            return new RedirectResponse($urlGenerator->generate('index'));
        }

        return new Response($twig->render('user/register.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}
