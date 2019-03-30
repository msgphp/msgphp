<?php

declare(strict_types=1);

namespace MsgPhp\User\Model;

use MsgPhp\User\Credential\Credential;
use MsgPhp\User\Event\Domain\ChangeCredential;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AbstractCredential
{
    /**
     * @var Credential
     */
    private $credential;

    public function getCredential(): Credential
    {
        return $this->credential;
    }

    private function onChangeCredentialEvent(ChangeCredential $event): bool
    {
        return ($this->credential)($event);
    }
}
