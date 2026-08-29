# Kumwe Conversion

**This package says what a conversion is. Extensions say what the rate is. The App says who may ask.**

Kumwe Conversion is the money and quantity conversion contract of the
[Kumwe](https://github.com/kumwe) family, extracted from
[Kumwe App](https://github.com/kumwe/app) as a drop-in replacement. It bundles the exact decimal
kernel, the typed money and quantity values, the converted value types, the conversion requests and
converters, the pipelines, and the provider ports that rate and unit-conversion extensions
implement. It ships **no rate and no conversion table of any kind** — rate sourcing is an extension
concern, by decision, forever.

## The rule

> **A converted amount is always marked as converted and carries its rate and its as-at instant** —
> everywhere it appears: screen, report, export, API response, event payload. A displayed price that
> silently drifts from its stored value is an audit defect, not a formatting choice.

Conversion is layered above stored exact values and never mutates them. In this package the rule is
enforced by the type system: `ConvertedMoneyValue` and `ConvertedQuantityValue` are unconstructible
without their rate or factor, their as-at instant, their provider identity, and their declared
rounding — the constructor recomputes the arithmetic, so a figure that cannot prove its own
provenance cannot exist, let alone be serialized. The full rules live in the [charter](CHARTER.md).

## Who implements what

- **This package** implements the contract: `ExactDecimal` and its arithmetic, `MoneyValue`,
  `QuantityValue`, the converted value types with their provenance, `MoneyConversionRequest` /
  `UnitConversionRequest`, the converters, the pipelines, and the provider and catalog ports.
- **Extensions** implement the provider ports — `MoneyRateProvider` for exchange rates,
  `UnitConversionProvider` for unit factors. An external rate service, a manually administered
  table, a bank feed, and a contractual fixed rate are all implementations of the same port.
- **Kumwe App** (consumer #1, never owner) implements the SPI that contributes providers into an
  installation, the runtime catalogs the pipelines consult, the storage of exact values, and every
  surface that renders a converted figure with its provenance.

## Status

**Extracted and published.** All twenty-three inventoried types are here — the exact decimal
kernel, the value types, the conversion contract, the pipelines, and the provider ports — proven
byte-for-byte against the Kumwe App originals and released on
[Packagist](https://packagist.org/packages/kumwe/conversion). The remaining phase, the App
consuming the package directly, is recorded in [`docs/roadmap.md`](docs/roadmap.md); the protocol
with the App is in [`docs/app-agreement.md`](docs/app-agreement.md). The check lane runs:

```bash
composer install
composer check   # metadata, API, autoload, style, PHPStan max, docs, and tests
```

The tests themselves remain dependency-free and can also be run directly with `php tests/run.php`.
The complete public shape of all twenty-three canonical types is frozen in
[`resources/public-api/v1.json`](resources/public-api/v1.json); `composer api` rejects drift.
Its `extension-provider-v1` profile names the fifteen-type transitive surface provider extensions
compile against, so the App can pin that package-owned evidence instead of maintaining a second copy.

## License

Licensed under the [Apache License, Version 2.0](LICENSE).
