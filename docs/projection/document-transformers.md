# Projection document transformers

A projection document transformer is bound to `MsgPhp\Domain\Projection\DomainProjectionDocumentTransformerInterface`.
Its purpose is to transform domain objects into [projection documents](documents.md).

## API

### `transform(object $object): DomainProjectionDocument`

Transforms the domain object into a projection document.

## Implementations

### `MsgPhp\Domain\Infra\Psr\DomainProjectionDocumentTransformer`

A PSR container tailored projection document transformer.

- [Read more](../infrastructure/psr-container.md#domain-projection-document-transformer)
