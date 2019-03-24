<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Form\Type;

use MsgPhp\User\Password\GenericPasswordHashing;
use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordHashing;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class HashedPasswordType extends AbstractType
{
    /**
     * @var PasswordHashing|null
     */
    private $hashing;

    public function __construct(PasswordHashing $hashing = null)
    {
        $this->hashing = $hashing;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $password = new Password($options['password_hashing'] ?? $this->hashing ?? new GenericPasswordHashing(), $this->createAlgorithm($options['password_algorithm']));
        $fieldOptions = [
            'required' => $options['required'],
            'translation_domain' => $options['translation_domain'],
            'invalid_message' => $options['invalid_message'],
            'invalid_message_parameters' => $options['invalid_message_parameters'],
        ];

        $builder
            ->addViewTransformer(new CallbackTransformer(function ($value): ?array {
                return null;
            }, function ($value) use ($password): ?string {
                $password->hash = null;

                if (!\is_array($value)) {
                    throw new TransformationFailedException();
                }

                if (\is_string($value['confirmation'] ?? null) && \function_exists('sodium_memzero')) {
                    sodium_memzero($value['confirmation']);
                }

                if (!isset($value['plain'])) {
                    return null;
                }

                if (!\is_string($value['plain'])) {
                    throw new TransformationFailedException();
                }

                $password->hash = $password->hashing->hash($value['plain'], $password->algorithm);

                if (\function_exists('sodium_memzero')) {
                    sodium_memzero($value['plain']);
                }

                return $password->hash;
            }))
            ->add('plain', PasswordType::class, $options['password_options'] + $fieldOptions)
        ;

        if ($options['password_confirm']) {
            if (!class_exists(Callback::class)) {
                throw new \LogicException('Password confirmation requires "symfony/validator".');
            }

            $passwordConfirmOptions = $options['password_confirm_options'] + $fieldOptions;
            $passwordConfirmOptions = self::withConstraint($passwordConfirmOptions, new Callback(function ($value, ExecutionContextInterface $context) use ($password, $passwordConfirmOptions): void {
                if (null === $value && null === $password->hash) {
                    return;
                }

                $valid = false;
                if (\is_string($value)) {
                    $valid = null === $password->hash ? false : $password->hashing->isValid($password->hash, $value, $password->algorithm);
                    if (\function_exists('sodium_memzero')) {
                        sodium_memzero($value);
                    }
                }

                if (!$valid) {
                    $context->buildViolation($passwordConfirmOptions['invalid_message'], $passwordConfirmOptions['invalid_message_parameters'])
                        ->setCause($this)
                        ->setInvalidValue($value)
                        ->setTranslationDomain($passwordConfirmOptions['translation_domain'])
                        ->addViolation()
                    ;
                }
            }));

            $builder->add('confirmation', PasswordType::class, $passwordConfirmOptions);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'password_hashing' => null,
            'password_algorithm' => null,
            'password_options' => [],
            'password_confirm' => false,
            'password_confirm_options' => function (Options $options, $value) {
                return $value ?? $options['password_options'];
            },
        ]);

        $resolver->setAllowedTypes('password_hashing', ['null', PasswordHashing::class]);
        $resolver->setAllowedTypes('password_algorithm', ['null', 'callable', 'int', 'string', PasswordAlgorithm::class]);
        $resolver->setAllowedTypes('password_confirm', ['bool']);
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
     * @param callable|int|string|PasswordAlgorithm|null $algorithm
     */
    private function createAlgorithm($algorithm): ?PasswordAlgorithm
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

        return \is_callable($algorithm) ? $algorithm() : null;
    }
}

/**
 * @internal
 */
final class Password
{
    /**
     * @var PasswordHashing
     */
    public $hashing;

    /**
     * @var PasswordAlgorithm|null
     */
    public $algorithm;

    /**
     * @var string|null
     */
    public $hash;

    public function __construct(PasswordHashing $hashing, ?PasswordAlgorithm $algorithm)
    {
        $this->hashing = $hashing;
        $this->algorithm = $algorithm;
    }
}
