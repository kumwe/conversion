# Kumwe Conversion

**Canonical contracts for exact money and quantity conversion. Providers supply rates; hosts supply
policy and wiring.**

Kumwe Conversion defines the money and quantity conversion contract for the
[Kumwe](https://github.com/kumwe) family. It bundles the exact decimal kernel, typed money and
quantity values, converted values, conversion requests and pipelines, and the provider ports that
rate and unit-conversion integrations implement. It ships **no rate and no conversion table of any
kind**: sourcing that evidence belongs to provider implementations.

## The rule

> **A converted amount is always marked as converted and carries its rate and its as-at instant** —
> everywhere it appears: screen, report, export, API response, or event payload.

Conversion is layered above stored exact values and never mutates them. `ConvertedMoneyValue` and
`ConvertedQuantityValue` cannot be constructed without their rate or factor, as-at instant,
provider identity, and declared rounding. Their constructors recompute the arithmetic, so a value
that cannot prove its own provenance is refused.

## Responsibilities

- **This package** implements `ExactDecimal` and its arithmetic, `MoneyValue`, `QuantityValue`, the
  converted value types and their evidence, conversion requests and pipelines, and the provider and
  catalog ports.
- **Provider integrations** implement `MoneyRateProvider` or `UnitConversionProvider`. External
  services, administered tables, feeds, and contractual rates are implementations behind those
  ports, never data bundled into this package.
- **Host applications** supply provider catalogs, authorization and ordering policy, persistence,
  and presentation. They consume the canonical package types directly.

## Installation and verification

Install the published library from
[Packagist](https://packagist.org/packages/kumwe/conversion):

```bash
composer require kumwe/conversion
```

For contributors, the complete package gate is:

```bash
composer install
composer check
```

The behavioural suite is dependency-free and can also be run directly with `php tests/run.php`.
The complete public shape of all twenty-three canonical types is recorded in
[`resources/public-api/v1.json`](resources/public-api/v1.json), and `composer api` rejects
unrecorded drift. Its `extension-provider-v1` profile identifies the exact fifteen-type transitive
surface required by provider implementations.

## License

Licensed under the [Apache License, Version 2.0](LICENSE).
