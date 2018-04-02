<?php

$typeUses = 'use Symfony\\Component\\Form\\Extension\\Core\\Type\\'.($fieldType = 'email' === $fieldName ? 'EmailType' : 'TextType').';';
$fields = "\$builder->add('${fieldName}', ${fieldType}::class);";

if ($hasPassword) {
    $typeUses .= "\nuse Symfony\\Component\\Form\\Extension\\Core\\Type\\PasswordType;";
    $fields .= "\n\$builder->add('password', PasswordType::class);";
}

return <<<PHP
<?php

namespace ${ns};

use Symfony\Component\Form\AbstractType;
${typeUses}
use Symfony\Component\Form\FormBuilderInterface;

final class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface \$builder, array \$options)
    {
        ${fields}
    }
}
PHP;
