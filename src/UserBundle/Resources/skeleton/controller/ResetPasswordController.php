<?php

declare(strict_types=1);

$uses = [
    'use '.$userClass.';',
    'use '.$formNs.'\\ResetPasswordType;',
    'use MsgPhp\\User\\Command\\ChangeUserCredentialCommand;',
    'use Doctrine\\ORM\\EntityManagerInterface;',
    'use SimpleBus\\SymfonyBridge\\Bus\\CommandBus;',
    'use Symfony\\Component\\Form\\FormFactoryInterface;',
    'use Symfony\\Component\\HttpFoundation\\Response;',
    'use Symfony\\Component\\HttpFoundation\\Response;',
    'use Symfony\\Component\\HttpFoundation\\Session\\Flash\\FlashBagInterface;',
    'use Twig\\Environment;',
];

$userShortName = false === ($i = strrpos($userClass, '\\')) ? $userClass : substr($userClass, $i + 1);

sort($uses);
$uses = implode("\n", $uses);

return <<<PHP
<?php

declare(strict_types=1);

namespace ${ns};

${uses}

final class ResetPasswordController
{
    public function __invoke(
        string \$token,
        Request \$request,
        FormFactoryInterface \$formFactory,
        FlashBagInterface \$flashBag,
        Environment \$twig,
        CommandBus \$bus,
        EntityManagerInterface \$em
    ): Response {
        \$form = \$formFactory->createNamed('', ResetPasswordType::class);
        \$form->handleRequest(\$request);

        if (\$form->isSubmitted() && \$form->isValid()) {
            \$user = \$em->getRepository(${userShortName}::class)->findOneBy(['passwordResetToken' => \$token]);
            \$bus->handle(new ChangeUserCredentialCommand(\$user->getId(), ['password' => \$form->get('password')->getData()]));
            \$flashBag->add('success', 'You\'re password is changed.');

            return new RedirectResponse('/');
        }

        return new Response(\$twig->render('${template}', [
            'form' => \$form->createView(),
        ]));
    }
}
PHP;
