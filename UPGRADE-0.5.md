# UPGRADE FROM 0.4 to 0.5

## Domain

- Renamed `DomainProjectionInterface` to `ProjectionInterface`
- Renamed `DomainProjectionDocument` to `ProjectionDocument`
- Renamed `DomainProjectionDocumentProvider` to `ProjectionDocumentProvider`
- Renamed `DomainProjectionDocumentTransformerInterface` to `ProjectionDocumentTransformerInterface`
- Updated `DomainProjectionRepositoryInterface::findAll(): DomainProjectionInterface[]` to `findAll(): ProjectionDocument[]`
- Updated `DomainProjectionRepositoryInterface::find(): ?DomainProjectionInterface` to `find(): ?ProjectionDocument`
- Renamed `DomainProjectionRepositoryInterface` to `ProjectionRepositoryInterface`
- Renamed `DomainProjectionSynchronization` to `ProjectionSynchronization`
- Renamed `DomainProjectionTypeRegistryInterface` to `ProjectionTypeRegistryInterface`
- Added default projection messages (`Command\` and `Event\`)

## UserBundle

- Updated `make:user` to generate code using Symfony Messenger instead of SimpleBus
- Disabled legacy credentials in `make:user` (`Email/NicknameSaltedPassword`)
