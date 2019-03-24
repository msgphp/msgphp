<?php

declare(strict_types=1);

namespace MsgPhp\User\Command;

use MsgPhp\User\UserId;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class AddUserRole
{
    /**
     * @var UserId
     */
    public $userId;

    /**
     * @var string
     */
    public $roleName;

    /**
     * @var array
     */
    public $context;

    final public function __construct(UserId $userId, string $roleName, array $context = [])
    {
        $this->userId = $userId;
        $this->roleName = $roleName;
        $this->context = $context;
    }
}
