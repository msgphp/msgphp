<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\Entity\Credential\Anonymous;
use MsgPhp\User\Entity\Features\AnonymousCredential;
use MsgPhp\User\UserIdInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class User
{
    use AnonymousCredential;

    private $id;

    public function __construct(UserIdInterface $id)
    {
        $this->id = $id;
        $this->credential = new Anonymous();
    }

    public function getId(): UserIdInterface
    {
        return $this->id;
    }
}
