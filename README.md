# Kumwe Conversion

[![Packagist version][badge-packagist-version-image]][badge-packagist-version-link]
[![CI][badge-ci-image]][badge-ci-link]
[![PHP][badge-php-image]][badge-php-link]
[![License][badge-license-image]][badge-license-link]

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

Requires PHP 8.5. Install the published library from
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

## API, Core integration and releases

Read the [complete API reference](docs/public-api.md), [construction and lifetime contract](docs/architecture.md),
[standalone integration guide](docs/integration.md) and [Core/App agreement](docs/app-agreement.md).
The library uses explicit construction; host-owned catalogs are never discovered or registered globally.
The [direct construction example](examples/direct-construction.php) shows pipeline composition.

The [public API manifest](resources/public-api/v1.json), capabilities and service map describe the
current package surface. Provider-profile consumers use the preserved
[compatibility manifest](resources/public-api/legacy-v1.json). `composer metadata` verifies ownership,
construction and [release-record](docs/release-record.md) digests; `composer clean-consumer` compares
every exported path and byte with Git and installs that ZIP as a fresh no-dev dependency.

Source quality checks additionally require Node.js 20+ and
`npm ci --prefix tools/schema-validator --ignore-scripts`. The pinned Ajv2020 gate validates
complete API, capability, service-map and release-record schemas. Production requires only PHP.

See [releases](https://github.com/kumwe/conversion/releases), [release policy](docs/releasing.md),
[changes](CHANGELOG.md) and [issues](https://github.com/kumwe/conversion/issues). Publication,
independent release verification and Core integration are separate observations.

[badge-packagist-version-image]: https://img.shields.io/packagist/v/kumwe/conversion
[badge-packagist-version-link]: https://packagist.org/packages/kumwe/conversion
[badge-ci-image]: https://github.com/kumwe/conversion/actions/workflows/ci.yml/badge.svg?branch=main
[badge-ci-link]: https://github.com/kumwe/conversion/actions/workflows/ci.yml
[badge-php-image]: https://img.shields.io/packagist/dependency-v/kumwe/conversion/php
[badge-php-link]: composer.json
[badge-license-image]: https://img.shields.io/packagist/l/kumwe/conversion
[badge-license-link]: LICENSE
