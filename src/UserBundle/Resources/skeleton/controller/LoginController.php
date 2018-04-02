<?php

if ($hasSecurity) {
    $securityUses = "\nuse Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationUtils;";
    $securityDeps = ",\n        AuthenticationUtils \$authenticationUtils";
    $formOptions = ", ['${fieldName}' => \$authenticationUtils->getLastUsername()]";
    $body = <<<'PHP'

        if (null !== $error = $authenticationUtils->getLastAuthenticationError(true)) {
            $form->addError(new FormError($error->getMessage(), $error->getMessageKey(), $error->getMessageData()));
        }
PHP;
} else {
    $securityUses = '';
    $securityDeps = '';
    $formOptions = '';
    $body = '';
}

return <<<PHP
<?php

namespace ${ns};

use {$formNs}\LoginType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;{$securityUses}
use Twig\Environment;

final class LoginController
{
    public function __invoke(
        Environment \$twig,
        FormFactoryInterface \$formFactory${securityDeps}
    ): Response {
        \$form = \$formFactory->createNamed('', LoginType::class${formOptions});
{$body}

        return new Response(\$twig->render('${template}', [
            'form' => \$form->createView(),
        ]));
    }
}
PHP;
