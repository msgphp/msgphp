<?php

declare(strict_types=1);

$extensionConfig = $serviceConfig = $sep = '';

if ($config) {
    $extensionConfig = <<<PHP
    \$container->extension('msgphp_user', ${config});
PHP;

}

foreach ($services as $service) {
    $serviceConfig .= $sep.'        '.str_replace("\n", "\n        ", $service);
    $sep = "\n\n";
}

if ($serviceConfig) {
    if ($extensionConfig) {
        $extensionConfig .= "\n\n";
    }

    $serviceConfig = <<<PHP
    \$container->services()
        ->defaults()
            ->private()
            ->autoconfigure()
            ->autowire()

${serviceConfig}
    ;
PHP;
}

return <<<PHP
<?php

use Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ContainerConfigurator;
use function Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ref;

return function (ContainerConfigurator \$container) {
${extensionConfig}${serviceConfig}
};

PHP;
