# The App agreement

This document records the working agreement between `kumwe/conversion` and its first consumer,
[Kumwe App](https://github.com/kumwe/app). It exists so that neither repository has to guess: the
App adopted this package as a drop-in replacement for code it had already published and pinned as
extension API, and the promises below are what keep "drop-in" true release after release.

## Roles

- **This package** owns the canonical classes: the exact decimal kernel, the value types, the
  conversion contract, the pipelines, and the provider ports, under `Kumwe\Conversion\`.
- **The App** owns adoption: the exact version pin, the `class_alias` shims, the extension SPI
  (registrars, definition types, admission bounds), the runtime catalogs, storage of exact values,
  and every surface that renders a converted figure with its provenance.
- **Extensions** own rates and conversion tables, behind the ports defined here, contributed
  through the App's SPI.

## The exact-pin protocol

1. **The App pins an exact version.** While this package is `0.x`, the App's `composer.json`
   requires an exact release, never a range. A contract change reaches the App only as a
   deliberate re-pin with its own review and evidence — the same discipline the App applies to its
   Studio pin.
2. **The pinned fixtures are the authority.** The App's compatibility fixtures —
   `tests/Fixtures/ExtensionApi/money-rate-provider-v1.json` and
   `tests/Fixtures/ExtensionApi/unit-conversion-provider-v1.json` — pin the extension-facing
   surface by FQCN and signature. They are never rewritten. A release of this package that would
   require editing either fixture is not adoptable at the current generation: an incompatible
   surface is a successor generation on the App side, with a new fixture beside the old one.
3. **Identity is proven on every re-pin.** The App's full suite, unchanged, is the acceptance test
   for a new pin. A pin that needs an App test edited is refused, and the difference is a finding
   in this repository.
4. **Version policy.** Patch: behaviour-identical fixes. Minor: additive surface the App may adopt
   without touching the pinned generation. Major: anything a consumer must act on. This package
   versions independently; alignment travels through the pin, never through matching numbers.

## The alias contract

The extension API was published under `Kumwe\App\...` names and those names are **pinned: they
never break**. The App carries a `class_alias` shim for every FQCN below, resolving the historical
name to the canonical class in this package. Aliases bind per class; they are loaded by the App's
bootstrap before any extension code runs; they exist for the life of the pinned generation —
which has no planned end.

Canonical name (here) ↔ historical name (aliased in the App):

| `Kumwe\Conversion\...` | `Kumwe\App\...` |
| --- | --- |
| `Decimal\ExactDecimal` | `BusinessRecord\Domain\ExactDecimal` |
| `Decimal\ExactDecimalArithmetic` | `BusinessRecord\Domain\ExactDecimalArithmetic` |
| `Decimal\ExactRoundingRule` | `BusinessRecord\Domain\ExactRoundingRule` |
| `Value\MoneyValue` | `BusinessRecord\Domain\MoneyValue` |
| `Value\QuantityValue` | `BusinessRecord\Domain\QuantityValue` |
| `Value\MoneyRoundingMode` | `BusinessRecord\Domain\MoneyRoundingMode` |
| `Value\QuantityRoundingMode` | `BusinessRecord\Domain\QuantityRoundingMode` |
| `Value\MoneyExchangeRate` | `BusinessRecord\Domain\MoneyExchangeRate` |
| `Value\UnitConversionFactor` | `BusinessRecord\Domain\UnitConversionFactor` |
| `Value\ConvertedMoneyValue` | `BusinessRecord\Domain\ConvertedMoneyValue` |
| `Value\ConvertedQuantityValue` | `BusinessRecord\Domain\ConvertedQuantityValue` |
| `Contract\MoneyConversionRequest` | `BusinessRecord\Domain\MoneyConversionRequest` |
| `Contract\UnitConversionRequest` | `BusinessRecord\Domain\UnitConversionRequest` |
| `Contract\MoneyConverter` | `BusinessRecord\Domain\MoneyConverter` |
| `Contract\QuantityConverter` | `BusinessRecord\Domain\QuantityConverter` |
| `Provider\MoneyRateProvider` | `BusinessRecord\Application\MoneyRateProvider` |
| `Provider\UnitConversionProvider` | `BusinessRecord\Application\UnitConversionProvider` |
| `Provider\MoneyRateProviderCatalog` | `BusinessRecord\Application\MoneyRateProviderCatalog` |
| `Provider\UnitConversionProviderCatalog` | `BusinessRecord\Application\UnitConversionProviderCatalog` |
| `Provider\MoneyRateUnavailable` | `BusinessRecord\Application\MoneyRateUnavailable` |
| `Provider\UnitConversionUnavailable` | `BusinessRecord\Application\UnitConversionUnavailable` |
| `Provider\MoneyConversionPipeline` | `BusinessRecord\Application\MoneyConversionPipeline` |
| `Provider\UnitConversionPipeline` | `BusinessRecord\Application\UnitConversionPipeline` |

Eight of these are pinned extension API v1 in the App's classification — `MoneyRateProvider` and
`UnitConversionProvider` (the ports extensions implement), `MoneyConversionRequest` and
`UnitConversionRequest` (received), `MoneyExchangeRate` and `UnitConversionFactor` (constructed),
and the two rounding-mode enums whose case sets the fixtures enumerate. The remaining fifteen are
aliased with the same permanence, because "published" is the bar, not "pinned": an extension or an
App module that imported any of them keeps compiling.

Not aliased, because they do not move: `MoneyRateProviderRegistrar`,
`UnitConversionProviderRegistrar`, `UnitConversionProviderDefinition`, and
`MoneyRateProviderDefinition` (SPI, App authority — note that the last lives in
`BusinessRecord\Domain` yet implements the App's `ContributionDefinition`), and the runtime
catalog implementations under `BusinessRecord\Infrastructure`.

## Rates never enter this package

This is the boundary both repositories enforce, from the App roadmap's decision D10 and ADR 0004:
"Core ships no rate table, no rate feed and no rate policy," and from D13.5 for units: "core ships
no conversion table."

- A rate, a factor, a currency table, a unit table, a default rate, a fallback rate, or a bundled
  provider of any kind in this package is a charter breach — a defect whatever its tests say — and
  the App refuses the release at the pin.
- Test vectors in this repository carry rates and factors as inputs to a proof; they are never
  exported, never reachable at runtime, and never a lookup.
- Rounding policy on conversion — which modes a business offers for a currency or a unit — is
  declared by the caller per request. This package defines the vocabulary and applies the declared
  mode exactly; it never chooses one.
- The rule's rendering half stays with the App: the `undeclared_currency_conversions` qualification
  objective, the report and export provenance carriage, and the write-path refusals
  (`RecordValueGuard`, `RecordValueCodec`) that keep a converted figure out of storage are App
  obligations this package makes possible but does not perform.

## Raising a need

A shape the contract cannot express is a finding raised in this repository, agreed under the pin
protocol, and delivered as a versioned release the App re-pins deliberately — never a local
workaround in the App, and never a quiet widening here. The reverse also holds: an App need that
is really SPI, storage, or presentation is the App's to build, and this package will not grow an
opinion about it.
