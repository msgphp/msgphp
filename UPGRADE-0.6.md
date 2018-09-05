# UPGRADE FROM 0.5 to 0.6

## Domain

- Updated `ProjectionRepositoryInterface::findAll(): iterable` to `findAll(): PaginatedDomainCollectionInterface`
- Added `PaginatedDomainCollection`
- Added `DomainCollectionFactory::createFromCallable()`

## Eav

- Marked `AttributeValue::getChecksum()` static, requiring `$value` as 1st argument
- Finalized `AttributeValue::get/changeValue()`

## User

- Added `SecurityUser::getUserId(): UserIdInterface`

## UserBundle

- Renamed default Twig variable

    Before: `{{ msgphp_user.id }}`
    After: `{{ msgphp_user.currentId }}` or `{{ app.user.userId }}`
