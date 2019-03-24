<?php

declare(strict_types=1);

namespace MsgPhp\User\Infrastructure\Security;

use MsgPhp\User\Password\PasswordAlgorithm;
use MsgPhp\User\Password\PasswordHashingInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PasswordHashing implements PasswordHashingInterface
{
    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    public function __construct(PasswordEncoderInterface $encoder)
    {
        if (!$encoder instanceof SelfSaltingEncoderInterface) {
            throw new \LogicException(sprintf('Only a self-salting password hashing method is supported, got "%s".', \get_class($encoder)));
        }

        $this->encoder = $encoder;
    }

    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string
    {
        if (null !== $algorithm) {
            throw new \LogicException('A custom password algorithm is not supported.');
        }

        $hash = $this->encoder->encodePassword($plainPassword, '');

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $hash;
    }

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool
    {
        if (null !== $algorithm) {
            throw new \LogicException('A custom password algorithm is not supported.');
        }

        $valid = $this->encoder->isPasswordValid($hashedPassword, $plainPassword, '');

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $valid;
    }
}
