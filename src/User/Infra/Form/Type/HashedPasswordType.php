<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Form\Type;

use MsgPhp\User\Password\{PasswordAlgorithm, PasswordHashingInterface, PasswordSalt};
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class HashedPasswordType extends AbstractType
{
    private $passwordHashing;
    private $tokenStorage;

    public function __construct(PasswordHashingInterface $passwordHashing, TokenStorageInterface $tokenStorage = null)
    {
        $this->passwordHashing = $passwordHashing;
        $this->tokenStorage = $tokenStorage;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $defaultOptions = [
            'required' => $options['required'],
            'invalid_message' => $options['invalid_message'],
            'invalid_message_parameters' => $options['invalid_message_parameters'],
        ];
        $passwordOptions = $options['password_options'] + $defaultOptions;
        $algorithm = $options['password_algorithm'];
        $plainPassword = '';

        if ($options['password_confirm_current']) {
            if (!class_exists(Callback::class)) {
                throw new \LogicException('Current password confirmation requires "symfony/validator".');
            }
            if (null === $this->tokenStorage) {
                throw new \LogicException('Current password confirmation requires "symfony/security".');
            }

            $passwordOptions = self::withConstraint($passwordOptions, new Callback(function (?string $value, ExecutionContextInterface $context) use ($algorithm, $passwordOptions, &$plainPassword): void {
                $token = $this->tokenStorage->getToken();

                if (null === $value || '' === $value || null === $token || !($user = $token->getUser()) instanceof UserInterface) {
                    $valid = false;
                } else {
                    $algorithm = null === $algorithm && null !== ($salt = $user->getSalt())
                        ? PasswordAlgorithm::createLegacySalted(new PasswordSalt($salt))
                        : (\is_callable($algorithm) ? $algorithm() : $algorithm);

                    $valid = $this->passwordHashing->isValid($user->getPassword(), $plainPassword, $algorithm);
                }

                if (!$valid) {
                    /** @var FormInterface $form */
                    $form = $context->getObject();
                    $form->addError(new FormError($passwordOptions['invalid_message'], $passwordOptions['invalid_message'], $passwordOptions['invalid_message_parameters'], null, $this));
                }
            }));
        }

        $builder->add('password', PasswordType::class, $passwordOptions);
        $builder->get('password')->addModelTransformer(new CallbackTransformer(function (?string $value): ?string {
            return null;
        }, function (?string $value) use ($algorithm, &$plainPassword): ?string {
            return null === $value ? null : $this->passwordHashing->hash($plainPassword = $value, \is_callable($algorithm) ? $algorithm() : $algorithm);
        }));

        if ($options['password_confirm']) {
            if (!class_exists(Callback::class)) {
                throw new \LogicException('Password confirmation requires "symfony/validator".');
            }

            $passwordConfirmOptions = ['mapped' => false] + $options['password_confirm_options'] + $defaultOptions;
            $passwordConfirmOptions = self::withConstraint($passwordConfirmOptions, new Callback(function (?string $value, ExecutionContextInterface $context) use ($algorithm, $passwordConfirmOptions): void {
                if (null === $value || '' === $value) {
                    return;
                }

                /** @var FormInterface $form */
                $form = $context->getObject();
                /** @var FormInterface $root */
                $root = $form->getParent();

                if (null === $password = $root->get('password')->getData()) {
                    return;
                }

                if (!$this->passwordHashing->isValid($password, $value, \is_callable($algorithm) ? $algorithm() : $algorithm)) {
                    /** @var FormInterface $form */
                    $form = $context->getObject();
                    $form->addError(new FormError($passwordConfirmOptions['invalid_message'], $passwordConfirmOptions['invalid_message'], $passwordConfirmOptions['invalid_message_parameters'], null, $this));
                }
            }));

            $builder->add('confirmation', PasswordType::class, $passwordConfirmOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'password_algorithm' => null,
            'password_confirm' => false,
            'password_confirm_current' => false,
            'password_confirm_options' => function (Options $options, $value) {
                return $value ?? $options['password_options'];
            },
            'password_options' => [],
        ]);

        $resolver->setAllowedTypes('password_algorithm', ['null', 'callable', PasswordAlgorithm::class]);
        $resolver->setAllowedTypes('password_confirm', ['bool']);
        $resolver->setAllowedTypes('password_confirm_current', ['bool']);
        $resolver->setAllowedTypes('password_confirm_options', ['null', 'array']);
        $resolver->setAllowedTypes('password_options', ['array']);
    }

    private static function withConstraint(array $options, Constraint $constraint): array
    {
        if (!isset($options['constraints'])) {
            $options['constraints'] = [];
        } elseif (!\is_array($options['constraints'])) {
            $options['constraints'] = [$options['constraints']];
        }

        $options['constraints'][] = $constraint;

        return $options;
    }
}
