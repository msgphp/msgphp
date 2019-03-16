<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\CredentialInterface;
use MsgPhp\User\Event\Domain\ChangeCredentialEvent;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractCredential
{
    /**
     * @var CredentialInterface
     */
    private $credential;

    private function handleChangeCredentialEvent(ChangeCredentialEvent $event): bool
    {
        if (!\is_callable($this->credential)) {
            throw new \LogicException(sprintf('Credential "%s" must be an invokable to apply event "%s".', \get_class($this->credential), \get_class($event)));
        }

        return ($this->credential)($event);
    }
}
