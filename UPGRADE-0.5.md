# UPGRADE FROM 0.4 to 0.5

## Domain

- Renamed `DomainProjectionDocument` to `ProjectionDocument`
- Renamed `DomainProjectionDocumentProvider` to `ProjectionDocumentProvider`
- Renamed `DomainProjectionSynchronization` to `ProjectionSynchronization`
- Updated `DomainProjectionRepositoryInterface::findAll(): DomainProjectionInterface[]` to `findAll(): ProjectionDocument[]`
- Updated `DomainProjectionRepositoryInterface::find(): ?DomainProjectionInterface` to `find(): ?ProjectionDocument`
- Added default domain messages (`Command\` and `Event\`)
