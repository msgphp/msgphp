<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\Password\{PasswordAlgorithm, PasswordHashingInterface};
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface as SymfonyPasswordHashingInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class PasswordHashing implements PasswordHashingInterface
{
    private $hashing;

    public function __construct(SymfonyPasswordHashingInterface $hashing)
    {
        $this->hashing = $hashing;
    }

    public function hash(string $plainPassword, PasswordAlgorithm $algorithm = null): string
    {
        $hash = $this->hashing->encodePassword($plainPassword, self::getSalt($algorithm));

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $hash;
    }

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool
    {
        $valid = $this->hashing->isPasswordValid($hashedPassword, $plainPassword, self::getSalt($algorithm));

        if (\function_exists('sodium_memzero')) {
            sodium_memzero($plainPassword);
        }

        return $valid;
    }

    private static function getSalt(?PasswordAlgorithm $algorithm): string
    {
        if (null === $algorithm) {
            return '';
        }

        return $algorithm->salt->token ?? '';
    }
}
