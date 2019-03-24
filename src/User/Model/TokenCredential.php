<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Token;
use MsgPhp\User\Event\Domain\ChangeCredential;

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

    public function getCredential(): Token
    {
        return $this->credential;
    }

    public function getToken(): string
    {
        return $this->credential->getUsername();
    }

    public function changeToken(string $token): void
    {
        ($this->credential)(new ChangeCredential([Token::getUsernameField() => $token]));
    }
}
