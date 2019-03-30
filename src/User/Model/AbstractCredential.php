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

    private function onChangeCredentialEvent(ChangeCredential $event): bool
    {
        if (!\is_callable($this->credential)) {
            throw new \LogicException(sprintf('Credential "%s" must be an invokable to apply event "%s".', \get_class($this->credential), \get_class($event)));
        }

        return ($this->credential)($event);
    }
}
