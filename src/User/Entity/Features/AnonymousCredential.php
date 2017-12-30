<?php

declare(strict_types=1);

namespace MsgPhp\User\Entity\Features;

use MsgPhp\User\Entity\Credential\Anonymous;

/**
 * @author Roland Franssen <franssen.roland@gmail.com>
 */
trait AnonymousCredential
{
    use AbstractCredential;

    /** @var Anonymous */
    private $credential;

    public function getCredential(): Anonymous
    {
        return $this->credential;
    }
}
