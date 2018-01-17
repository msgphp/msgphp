<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
final class AddUserSecondaryEmailCommand
{
    public $userId;
    public $email;
    public $confirm;
    public $context;

    public function __construct(UserIdInterface $userId, string $email, bool $confirm = false, array $context = [])
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->confirm = $confirm;
        $this->context = $context;
    }
}
