<?php return <<<PHP
<?php

namespace ${ns};

use Doctrine\ORM\Mapping as ORM;
use MsgPhp\User\Entity\UserRole as BaseUserRole;

/**
 * @ORM\Entity()
 * @ORM\AssociationOverrides({
 *     @ORM\AssociationOverride(name="user", inversedBy="roles")
 * })
 *
 * @final
 */
class UserRole extends BaseUserRole
{
}
PHP;
