<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\CredentialInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractCredential
{
    /** @var CredentialInterface */
    private $credential;

    public function getCredential(): CredentialInterface
    {
        return $this->credential;
    }
}
