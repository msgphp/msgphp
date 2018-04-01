<?php

namespace App\Form\User;

use MsgPhp\User\Infra\Form\Type\HashedPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('currentPassword', HashedPasswordType::class, [
            'password_confirm_current' => true,
        ]);
        $builder->add('newPassword', HashedPasswordType::class, [
            'password_confirm' => true,
            'password_options' => ['constraints' => new NotBlank()],
        ]);
    }
}
