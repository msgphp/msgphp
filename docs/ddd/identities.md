# Identities

A domain identity is a composite value of one or more individual identifier values, indexed by an identifier field name.
Its usage is to uniquely identify a domain object, and therefor qualifying it an entity object.

Identifier values can be of any type, e.g. a [domain identifier](identifiers.md), another (foreign) entity object, or
any primitive value.

To ease working with the [identity mapping](identity-mapping.md) one can use a `MsgPhp\Domain\DomainIdentityHelper`
domain service.

## API

### `isIdentifier($value): bool`

Tells if `$value` is a known identifier value. This is either a [domain identifier](identifiers.md) object or an entity
object.

---

### `isEmptyIdentifier($value): bool`

Tells if `$value` is a known empty identifier value. It returns `true` if the specified value is either `null`, an empty
[domain identifier](identifiers.md) or an entity object without its identity set.

---

### `normalizeIdentifier($value)`

Returns the primitive identifier value of `$value`. Empty identifier values (see `isEmptyIdentifier()`) are normalized
as `null`, a [domain identifier](identifiers.md) as string value and an entity object as normalized identity value.
A value of any other type is returned as is.

---

### `getIdentifiers(object $object): array`

Returns the actual identifier values of `$object`.

---

### `getIdentifierFieldNames(string $class): array`

Returns the identifier field names for `$class`. Any instance should have an identity composed of these field values.
See also `DomainIdentityMappingInterface::getIdentifierFieldNames()`.

---

### `isIdentity(string $class, $value): bool`

Tells if `$value` is a valid identity for type `$class`. An identity value is considered valid if an entity object uses
a single identifier value as identity and `$value` is a non empty identifier (see `isEmptyIdentifier()`).

In case of one or more identifier values, given in the form of an array, its keys must exactly match the available
identifier field names and its values must contain no empty identifiers.

---

### `toIdentity(string $class, $value): array`

Returns a composite identity value for `$class` from `$value`.

---

### `getIdentity(object $object): array`

Returns the actual, non empty, identifier values of `$object`. Each identifier value is keyed by its corresponding
identifier field name. ee also `DomainIdentityMappingInterface::getIdentity()`.
