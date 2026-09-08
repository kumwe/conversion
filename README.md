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
[`resources/public-api/legacy-v1.json`](resources/public-api/legacy-v1.json), and `composer api` rejects
unrecorded drift. Its `extension-provider-v1` profile identifies the exact fifteen-type transitive
surface required by provider implementations.

## License

Licensed under the [Apache License, Version 2.0](LICENSE).

## Version 2 adoption handoff

The [migration handoff](MIGRATION-HANDOFF.md) records the exact package ownership, historical rename map, public manifests and next consumer task. Read the [complete public API reference](docs/public-api.md), [construction/lifetime decision](docs/architecture.md) and [standalone integration guide](docs/integration.md). The library uses explicit direct construction; host-owned catalogs are never discovered or registered globally. Independent post-publication verification produces the external release attestation; this release does not claim App adoption or native cutover.

The governed [public API manifest](resources/public-api/v1.json) uses the standard `kumwe-package-public-api/v1` schema for automatic package adoption. The previous API/profile document remains byte-identical at [the legacy compatibility path](resources/public-api/legacy-v1.json). `composer metadata` checks the canonical projection, ownership, construction rationale and handoff hashes; `composer clean-consumer` compares every exported path and byte against Git and installs that ZIP as a fresh no-dev dependency.

Source quality checks also require Node.js 20+ and `npm ci --prefix tools/schema-validator --ignore-scripts`. The pinned Ajv2020 gate validates the complete authoritative API, capability, service-map and handoff schemas before release. Production consumers still require only PHP.
