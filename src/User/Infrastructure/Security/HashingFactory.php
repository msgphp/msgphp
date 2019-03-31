<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\Password\GenericPasswordHashing;
use MsgPhp\User\Password\PasswordAlgorithm;
use Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class HashingFactory implements EncoderFactoryInterface
{
    /**
     * @var EncoderFactoryInterface
     */
    private $factory;

    public function __construct(EncoderFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function getEncoder($identity): PasswordEncoderInterface
    {
        $algorithm = null;

        if (UserIdentity::class === $identity || ($identity instanceof UserIdentity && null === $algorithm = $identity->getPasswordAlgorithm())) {
            $encoder = $this->factory->getEncoder($identity);

            if (!$encoder instanceof SelfSaltingEncoderInterface) {
                throw new \LogicException(sprintf('Only a self-salting password hashing method is supported, got "%s".', \get_class($encoder)));
            }

            return $encoder;
        }

        if (null === $algorithm) {
            return $this->factory->getEncoder($identity);
        }

        if ($algorithm->legacy) {
            return new MessageDigestPasswordEncoder((string) $algorithm->type, $algorithm->encodeBase64, $algorithm->salt->iterations ?? 5000);
        }

        switch ($algorithm->type) {
            case \defined('PASSWORD_ARGON2I') ? \PASSWORD_ARGON2I : 2:
                // Symfony 4.1
                static $sf41;
                if (null === $sf41) {
                    $sf41 = (new \ReflectionClass(Argon2iPasswordEncoder::class))->hasProperty('configs');
                }
                if ($sf41) {
                    return new Argon2iPasswordEncoder((int) $algorithm->options['memory_cost'] ?? \PASSWORD_ARGON2_DEFAULT_MEMORY_COST, (int) $algorithm->options['time_cost'] ?? \PASSWORD_ARGON2_DEFAULT_TIME_COST, (int) $algorithm->options['threads'] ?? \PASSWORD_ARGON2_DEFAULT_THREADS);
                }
                // no break
            case \PASSWORD_BCRYPT:
                return new BCryptPasswordEncoder((int) $algorithm->options['cost'] ?? \PASSWORD_BCRYPT_DEFAULT_COST);
        }

        return new class($algorithm) implements PasswordEncoderInterface, SelfSaltingEncoderInterface {
            /**
             * @var PasswordAlgorithm
             */
            private $algorithm;

            public function __construct(PasswordAlgorithm $algorithm)
            {
                $this->algorithm = $algorithm;
            }

            public function encodePassword($raw, $salt): string
            {
                return (new GenericPasswordHashing())->hash($raw, $this->algorithm);
            }

            public function isPasswordValid($encoded, $raw, $salt): bool
            {
                return (new GenericPasswordHashing())->isValid($encoded, $raw, $this->algorithm);
            }
        };
    }
}
