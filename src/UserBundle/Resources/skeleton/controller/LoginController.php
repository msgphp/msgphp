<?php

$uses = [
    'use '.$formNs.'\\LoginType;',
    'use Symfony\\Component\\Form\\FormFactoryInterface;',
    'use Symfony\\Component\\HttpFoundation\\Response;',
    'use Twig\\Environment;',
];

if ($hasSecurity) {
    $uses[] = 'use Symfony\\Component\\Form\\FormError;';
    $uses[] = 'use Symfony\\Component\\Security\\Http\\Authentication\\AuthenticationUtils;';
    $securityDeps = ",\n        AuthenticationUtils \$authenticationUtils";
    $formOptions = ", ['${fieldName}' => \$authenticationUtils->getLastUsername()]";
    $body = <<<'PHP'

        if (null !== $error = $authenticationUtils->getLastAuthenticationError(true)) {
            $form->addError(new FormError($error->getMessage(), $error->getMessageKey(), $error->getMessageData()));
        }
PHP;
} else {
    $securityDeps = '';
    $formOptions = '';
    $body = '';
}

sort($uses);
$uses = implode("\n", $uses);

return <<<PHP
<?php

namespace ${ns};

${uses}

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
