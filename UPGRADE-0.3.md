# UPGRADE FROM 0.2 to 0.3

## Domain

- Added `%msgphp.domain.class_mapping%` DI parameter to inject the full domain class mapping

## User

- Enabled role `ROLE_DEFAULT` for users by default if no `UserRolesProviderInterface` is registered

## UserBundle

- Added `make:user` when [`MakerBundle`](https://github.com/symfony/maker-bundle) is enabled. Consider (re-)generating your application code.

