<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

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
    private $hashing;

    public function __construct(PasswordEncoderInterface $hashing)
    {
        if (!$hashing instanceof SelfSaltingEncoderInterface) {
            throw new \LogicException(sprintf('Only a self-salting password hashing method is supported, got "%s".', \get_class($hashing)));
        }

        $this->hashing = $hashing;
    }

    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string
    {
        if (null !== $algorithm) {
            throw new \LogicException('A custom password algorithm is not supported.');
        }

        $hash = $this->hashing->encodePassword($plainPassword, '');

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

        $valid = $this->hashing->isPasswordValid($hashedPassword, $plainPassword, '');

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $valid;
    }
}
