<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Token;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait TokenCredential
{
    use AbstractCredential;

    /** @var Token */
    private $credential;

    public function getCredential(): Token
    {
        return $this->credential;
    }

    public function getToken(): string
    {
        return $this->credential->getToken();
    }

    public function changeToken(string $token): void
    {
        $this->credential = $this->credential->withToken($token);
    }
}
