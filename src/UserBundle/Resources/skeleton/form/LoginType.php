<?php

declare(strict_types=1);

$fieldType = 'email' === $fieldName ? 'EmailType' : 'TextType';
$uses = [
    'use Symfony\\Component\\Form\\AbstractType;',
    'use Symfony\\Component\\Form\\Extension\\Core\\Type\\'.$fieldType.';',
    'use Symfony\\Component\\Form\\FormBuilderInterface;',
];

$fields = <<<PHP
        \$builder->add('${fieldName}', ${fieldType}::class);
PHP;

if ($hasPassword) {
    $uses[] = 'use Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType;';
    $fields .= <<<PHP

        \$builder->add('password', PasswordType::class);
PHP;
}

sort($uses);
$uses = implode("\n", $uses);

return <<<PHP
<?php

declare(strict_types=1);

namespace ${ns};

${uses}

final class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface \$builder, array \$options)
    {
${fields}
    }
}
PHP;
