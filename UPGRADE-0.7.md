# UPGRADE FROM 0.6 to 0.7

## User

- Marked `Username` abstract
- Renamed CLI command `user:synchronize:usernames` to `user:synchronize-usernames`
- Replaced `MsgPhp\User\Infra\Security\UserRolesProviderInterface` by `MsgPhp\User\Role\RoleProviderInterface`

## UserBundle

- Replaced generated `App\Security\UserRolesProvider` with config:

    ```yaml
    msgphp_user:
        # ...
        role_providers:
            #- default: [ROLE_USER]
            - MsgPhp\User\Role\UserRoleProvider
    ```

- Create and configure the `Username` entity in case `msgphp_php.username_lookup` is configured

    ```yaml
    msgphp_user:
        class_mapping:
            MsgPhp\User\Entity\Username: App\Entity\User\Username
        username_lookup:
            - # ...
    ```

    ```php
    <?php

    namespace App\Entity\User;

    use Doctrine\ORM\Mapping as ORM;
    use MsgPhp\User\Entity\Username as BaseUsername;

    /**
     * @ORM\Entity()
     */
    class Username extends BaseUsername
    {
    }
    ```
