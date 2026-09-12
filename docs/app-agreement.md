# The App agreement

This document defines the contract between `kumwe/conversion` and
[Kumwe Core/App](https://github.com/kumwe/app): exact dependency pins, canonical API ownership,
provider compatibility and host responsibilities.

## Roles

- **This package** owns the canonical classes: the exact decimal kernel, the value types, the
  conversion contract, the pipelines, and the provider ports, under `Kumwe\Conversion\`.
- **The App** owns adoption: the exact version pin, trusted activation and admission,
  runtime catalogue adapters, storage of exact values, and every surface that
  renders a converted figure with its provenance.
- **Conversion Extension** owns portable provider definitions; **Extension SDK** owns their
  signed manifest declarations and executable bindings. The old registrar interfaces are withdrawn.
- **Extensions** own rates and conversion tables, behind the ports defined here, contributed
  through the App's SPI.

## The exact-pin protocol

1. **The App pins an exact version.** While this package is `0.x`, the App's `composer.json`
   requires an exact release, never a range. A contract change reaches the App only as a
   deliberate re-pin with its own review and evidence — the same discipline the App applies to its
   Studio pin.
2. **The package manifest is the authority.** This package's `resources/public-api/legacy-v1.json` owns
   every canonical public type shape and the `extension-provider-v1` profile. The App copies none
   of those shapes: its consumer record stores only `kumwe/conversion`, the profile identifier, the
   reviewed digest, and the type count. On every build its consumer gate recomputes the digest from
   the installed manifest, verifies the complete ordered closure, and loads every selected type
   without an alias. A release whose profile does not match that record is not adoptable; an
   incompatible provider-facing surface requires a deliberately reviewed successor profile and App
   consumer record.
3. **Identity is proven on every re-pin.** The package-owned conformance corpus and public-API
   manifest are the first acceptance test. The App may delete only tests that duplicate that corpus;
   its retained unit, integration, functional, and architecture assertions remain unchanged. A pin
   that needs a retained App assertion edited is refused, and the difference is a finding in this
   repository.
4. **Version policy.** Patch: behaviour-identical fixes. Minor: additive surface the App may adopt
   without touching the pinned generation. Major: anything a consumer must act on. This package
   versions independently; alignment travels through the pin, never through matching numbers.

## The rename record

The canonical `Kumwe\Conversion\` names are **the only names**. The App's adoption change migrates
every reference — imports, FQCN strings, and docblocks — to the canonical names, deletes its copies
and obsolete historical surface records, and records the package profile digest and type count. It
retires the historical `Kumwe\App\...` names in that same change. This one-time contract reset is
legitimate because no third-party extension was ever published against the historical names. No
compatibility layer, no maintenance surface: nothing resolves a retired name. The table below
remains as the historical record of what moved where; each canonical name is under
`Kumwe\Conversion\`, each retired name was under `Kumwe\App\`.

| Canonical name (here) | Retired name (removed from the App) |
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

The package's `resources/public-api/legacy-v1.json` owns the extension-facing
`extension-provider-v1` profile. Its two roots are `MoneyRateProvider` and
`UnitConversionProvider`; its fifteen-type closure also includes the request and evidence classes,
both rounding modes and their shared rule, exact-decimal construction, the money and quantity values,
and the two typed refusals. The App pins the profile digest and reads this package evidence rather
than copying those class shapes into a second authority. The App's consumer record identifies the
canonical package and profile, its reviewed digest, and the fifteen-type count. The remaining eight
package types are not extension-provider surface, but every historical App name is retired with the
same finality: no published third-party consumer exists to break.

Canonical ownership assigns `UnitConversionProviderDefinition` and `MoneyRateProviderDefinition`
to `kumwe/conversion-extension` under `Kumwe\Conversion\Contribution`. SDK signed
`contributions.integration.rate_providers` and `unit_converters` declarations, together with
`ExtensionBindingRegistrar::moneyRateProvider()` and `unitConversionProvider()`, supersede the old
registrar interfaces. Runtime catalogue adapters under `BusinessRecord\Infrastructure` remain
App-owned. The original extraction inventory must not be read as a permanent App ownership rule
for those portable definitions.

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

Version 0.1.4 reserves `resources/public-api/v1.json` for the standard governed API schema. A later App adoption must update any existing profile reader to `resources/public-api/legacy-v1.json` while retaining the established `extension-provider-v1` digest and closure. The compatibility file preserves the 0.1.3 profile bytes exactly; runtime types and signatures do not change.
