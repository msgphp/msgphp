<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserEmail
{
    /**
     * @var UserId
     */
    public $userId;

    /**
     * @var string
     */
    public $email;

    /**
     * @var array
     */
    public $context;

    final public function __construct(UserId $userId, string $email, array $context = [])
    {
        $this->userId = $userId;
        $this->email = $email;
        $this->context = $context;
    }
}
