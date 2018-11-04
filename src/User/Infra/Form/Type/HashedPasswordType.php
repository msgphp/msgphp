<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Form\Type;

use MsgPhp\User\Password\{PasswordAlgorithm, PasswordHashingInterface, PasswordSalt};
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
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
        $defaultOptions = ['required' => $options['required']];
        if (isset($options['invalid_message'])) {
            $defaultOptions += [
                'invalid_message' => $options['invalid_message'],
                'invalid_message_parameters' => $options['invalid_message_parameters'],
            ];
        }
        $passwordOptions = $options['password_options'] + $defaultOptions;
        $algorithm = $options['password_algorithm'];

        /** @var mixed $plainPassword */
        $plainPassword = null;

        if ($confirmCurrent = $options['password_confirm_current']) {
            if (!class_exists(Callback::class)) {
                throw new \LogicException('Current password confirmation requires "symfony/validator".');
            }
            if (null === $this->tokenStorage) {
                throw new \LogicException('Current password confirmation requires "symfony/security".');
            }

            $passwordOptions = self::withConstraint($passwordOptions, new Callback(function ($value, ExecutionContextInterface $context) use ($passwordOptions, &$algorithm, &$plainPassword): void {
                $currentPassword = $this->getCurrentPassword();
                $algorithm = $this->createAlgorithm($algorithm, true);
                $valid = null !== $currentPassword && \is_string($plainPassword) && $this->passwordHashing->isValid($currentPassword, $plainPassword, $algorithm);
                unset($algorithm); // reference

                if (\is_string($plainPassword) && \function_exists('sodium_memzero')) {
                    sodium_memzero($plainPassword);
                }
                unset($plainPassword); // reference

                if (!$valid) {
                    /** @var FormInterface $form */
                    $form = $context->getObject();
                    $form->addError($this->createError($passwordOptions));
                }
            }));
        }

        $builder->add('password', PasswordType::class, $passwordOptions);
        $builder->get('password')->addModelTransformer(new CallbackTransformer(function ($value): ?string {
            return null;
        }, function ($value) use ($confirmCurrent, &$algorithm, &$plainPassword): ?string {
            $algorithm = $this->createAlgorithm($algorithm, $confirmCurrent);
            $plainPassword = $value;

            if (null === $value || !\is_string($value)) {
                unset($algorithm, $plainPassword); // reference

                if (null !== $value) {
                    throw new TransformationFailedException();
                }

                return null;
            }

            $hashed = $this->passwordHashing->hash($plainPassword, $algorithm);
            unset($algorithm); // reference

            if (\function_exists('sodium_memzero')) {
                sodium_memzero($value);
                sodium_memzero($plainPassword);
            }
            unset($plainPassword); // reference

            return $hashed;
        }));

        if ($options['password_confirm']) {
            if (!class_exists(Callback::class)) {
                throw new \LogicException('Password confirmation requires "symfony/validator".');
            }

            $passwordConfirmOptions = ['mapped' => false] + $options['password_confirm_options'] + $defaultOptions;
            $passwordConfirmOptions = self::withConstraint($passwordConfirmOptions, new Callback(function ($value, ExecutionContextInterface $context) use ($passwordConfirmOptions, $confirmCurrent, &$algorithm): void {
                /** @var FormInterface $form */
                $form = $context->getObject();
                /** @var FormInterface $root */
                $root = $form->getParent();
                $password = $root->get('password')->getData();
                $algorithm = $this->createAlgorithm($algorithm, $confirmCurrent);

                if (null === $value && null === $password) {
                    unset($algorithm); // reference

                    return;
                }

                $valid = \is_string($value) && \is_string($password) && $this->passwordHashing->isValid($password, $value, $algorithm);
                unset($algorithm); // reference

                if (\is_string($value) && \function_exists('sodium_memzero')) {
                    sodium_memzero($value);
                }
                if (!$valid) {
                    $form->addError($this->createError($passwordConfirmOptions));
                }
            }));

            $builder->add('confirmation', PasswordType::class, $passwordConfirmOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'label' => false,
            'password_algorithm' => null,
            'password_options' => [],
            'password_confirm' => false,
            'password_confirm_options' => function (Options $options, $value) {
                return $value ?? $options['password_options'];
            },
            'password_confirm_current' => false,
        ]);

        $resolver->setAllowedTypes('password_algorithm', ['null', 'callable', 'int', 'string', PasswordAlgorithm::class]);
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

    /**
     * @param null|callable|int|string|PasswordAlgorithm $algorithm
     */
    private function createAlgorithm($algorithm, bool $current = false): ?PasswordAlgorithm
    {
        if ($algorithm instanceof PasswordAlgorithm) {
            return $algorithm;
        }

        if (\is_int($algorithm)) {
            return PasswordAlgorithm::create($algorithm);
        }

        if (\is_string($algorithm)) {
            return PasswordAlgorithm::createLegacy($algorithm);
        }

        if ($current) {
            if (null === $this->tokenStorage) {
                throw new \LogicException('Current password confirmation requires "symfony/security".');
            }
            $token = $this->tokenStorage->getToken();
            $user = null === $token ? null : $token->getUser();

            if (\is_callable($algorithm)) {
                return $algorithm($user);
            }

            if ($user instanceof UserInterface && null !== $salt = $user->getSalt()) {
                return PasswordAlgorithm::createLegacySalted(new PasswordSalt($salt));
            }

            return null;
        }

        return \is_callable($algorithm) ? $algorithm() : null;
    }

    private function createError(array $options): FormError
    {
        $message = $options['invalid_message'] ?? 'This value is not valid.';

        return new FormError($message, $message, $options['invalid_message_parameters'] ?? [], null, $this);
    }

    private function getCurrentPassword(): ?string
    {
        if (null === $this->tokenStorage) {
            throw new \LogicException('Current password confirmation requires "symfony/security".');
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return null;
        }

        $user = $token->getUser();

        return $user instanceof UserInterface ? $user->getPassword() : null;
    }
}
