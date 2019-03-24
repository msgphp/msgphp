<?php

declare(strict_types=1);

namespace MsgPhp\User\Event;

use MsgPhp\User\User;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class UserCreated
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var array
     */
    public $context;

    final public function __construct(User $user, array $context)
    {
        $this->user = $user;
        $this->context = $context;
    }
}
