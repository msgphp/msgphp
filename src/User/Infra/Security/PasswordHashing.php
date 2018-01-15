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
        return $this->hashing->encodePassword($plainPassword, self::getSalt($algorithm));
    }

    public function isValid(string $hashedPassword, string $plainPassword, PasswordAlgorithm $algorithm = null): bool
    {
        return $this->hashing->isPasswordValid($hashedPassword, $plainPassword, self::getSalt($algorithm));
    }

    private static function getSalt(PasswordAlgorithm $algorithm = null): string
    {
        return null === $algorithm || null === $algorithm->salt || empty($algorithm->salt->token) ? '' : $algorithm->salt->token;
    }
}
