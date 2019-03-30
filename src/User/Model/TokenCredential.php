<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Token;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait TokenCredential
{
    use AbstractCredential;

    /**
     * @var Token
     */
    private $credential;

    public function getToken(): string
    {
        return $this->credential->getUsername();
    }
}
