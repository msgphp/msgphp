# UPGRADE FROM 0.3 to 0.4

## Eav

- Refactored `Entity\Fields\AttributeValueField` into `Entity\Features\EntityAttributeValue`
- Added `Infra\Doctrine\Repository\EntityAttributeValueRepositoryTrait`
- Added default domain messages (`Command\` and `Event\`)

## User

- Renamed `UserAttributeValue::getAttributeValueId()` to `getId()`
- Added `UserAttributeValue::changeValue()`
- Deleted `UserAttributeValue::getAttributeValue()`
- Renamed `ChangeUserAttributeValueCommand::$attributeValueId` to `$id`
- Renamed `DeleteUserAttributeValueCommand::$attributeValueId` to `$id`

## UserBundle

- Added support for Symfony Messenger and is favored over SimpleBus by default
- Renamed default Twig variables

    Before:

    ```
    {# Nullable #}
    {{ msgphp_user.user }}
    {{ msgphp_user.userId }}
    ```

    After:

    ```
    {# Not nullable #}
    {{ msgphp_user.current }}
    {{ msgphp_user.id }}
    ```
