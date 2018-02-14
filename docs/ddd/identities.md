# Identities

A domain identity is a composite value of individual identifier values. Its usage is to uniquely identify a domain
object, and therefor qualifying it an entity object.

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

Tells if `$value` is an empty identifier value. It returns `true` if the specified value is either an empty
[domain identifier](identifiers.md) or an entity object without identity.

---

### `normalizeIdentifier($value)`

Returns the primitive identifier value of `$value`. Empty identifier values (see above) are normalized as `null`,
otherwise a [domain identifier](identifiers.md) is returns its string value whereas an entity object returns its
identity value. All other types values are returned as is.

---

### `getIdentifiers(object $object): array`

Returns the actual identifier values of `$object`.

---

### `getIdentifierFieldNames(string $class): array`

Returns the identifier field names for `$class`. Any instance should have an identity composed of these field values.
See also `DomainIdentityMappingInterface::getIdentifierFieldNames()`.

---

### `isIdentity(string $class, array $value): bool`

Tells if `$value` is a valid identity for type `$class`. An identity value is considered valid if it's exactly indexed
with all available identifier field names.

---

### `toIdentity(string $class, $id, ...$idN): ?array`

Returns an identity value for `$classs` from individual identifier values, or `null` if the final identity is invalid.
To get a valid identity the number of given identifies must exactly match the number of available identifier field
names.

---

### `getIdentity(object $object): ?array`

Returns the actual identifier values of `$object`, or `null` if the object has no identity set. Each identifier value is
keyed by its corresponding identifier field name. See also `DomainIdentityMappingInterface::getIdentity()`.
