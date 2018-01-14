<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity;

use MsgPhp\User\{CredentialInterface, UserIdInterface};
use MsgPhp\User\Entity\Credential\Anonymous;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
class User
{
    private $id;
    private $credential;

    public function __construct(UserIdInterface $id)
    {
        $this->id = $id;
        $this->credential = new Anonymous();
    }

    public function getId(): UserIdInterface
    {
        return $this->id;
    }

    /**
     * @return CredentialInterface
     */
    public function getCredential()
    {
        return $this->credential;
    }
}
