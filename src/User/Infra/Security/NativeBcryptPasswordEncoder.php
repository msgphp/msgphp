<?php

declare(strict_types=1);

namespace MsgPhp\User\Infra\Security;

use MsgPhp\User\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface as BasePasswordEncoder;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class NativeBcryptPasswordEncoder implements PasswordEncoderInterface
{
    private $cost;

    public function __construct(int $cost = 13)
    {
        $this->cost = $cost;
    }

    public function encode(string $plainPassword): string
    {
        return password_hash($plainPassword, \PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }

    public function isValid(string $encodedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $encodedPassword);
    }
}
