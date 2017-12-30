<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\Domain\Entity\Features\AbstractUpdated;
use MsgPhp\User\CredentialInterface;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @internal
 */
trait AbstractCredential
{
    use AbstractUpdated;

    /** @var CredentialInterface */
    private $credential;

    public function getCredential(): CredentialInterface
    {
        return $this->credential;
    }
}
