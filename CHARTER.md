# The Conversion charter

**Kumwe Conversion** is the money and quantity conversion contract of the Kumwe family: the exact
decimal kernel, the typed money and quantity values, the converted value types that cannot exist
without their own provenance, the conversion requests, the converters and pipelines, and the
provider ports that rate and conversion-table extensions implement. It is extracted from
[Kumwe App](https://github.com/kumwe/app) as a **drop-in replacement, not a mutation**: the App
adopts this package and behaves identically, byte for byte, to the code it replaces.

This charter is normative for the repository. A change that contradicts it is a defect, whatever
tests it passes.

## What Conversion is

1. **The conversion contract, implemented once.** Two extensions converting the same amount produce
   results with the same shape and the same provenance because neither of them owns the step that
   produces it. This package is that step. The App roadmap records the decision (D10): "Core owns
   money-with-currency as a type and the conversion contract. Rate providers and rate sourcing are
   extension and integration concerns: an external rate service plugs into a pipeline rather than
   being wired into core." The identical shape holds for quantities (D13.5): "core owns the typed
   quantity-with-unit and the conversion contract; extensions own conversion tables."
2. **Exact, or refused.** The `ExactDecimal` kernel is bundled here because the contracts are
   unconstructible without it — a converted amount proves its own arithmetic in its constructor, and
   that proof is exact digit arithmetic, never floating point. A separate exact-arithmetic package
   was considered and rejected; the kernel travels with the only contract that needs it.
3. **Proven by construction and refusal.** Every value type demonstrates what it admits and what it
   refuses; every pipeline demonstrates that provenance survives the trip. A capability the suite
   does not prove is not claimed.

## What Conversion contains

- **The exact decimal kernel**: `ExactDecimal`, `ExactDecimalArithmetic`, `ExactRoundingRule` —
  fixed-scale base-10 values as canonical strings, exact multiplication, and declared rounding.
- **The value types**: `MoneyValue` (an exact amount bound to its ISO 4217 currency) and
  `QuantityValue` (an exact amount bound to its bounded portable unit identifier).
- **The converted value types**: `ConvertedMoneyValue` and `ConvertedQuantityValue` — types that are
  unconstructible without their rate or factor, their as-at instant, their provider identity, and
  their declared rounding, and whose constructors recompute the arithmetic so a figure that cannot
  prove its own provenance cannot exist.
- **The evidence types**: `MoneyExchangeRate` and `UnitConversionFactor` — a rate or factor bound to
  the pair it prices, the instant it was as at, and the provider that stands behind it — with the
  rounding vocabularies `MoneyRoundingMode` and `QuantityRoundingMode`.
- **The conversion requests and converters**: `MoneyConversionRequest`, `UnitConversionRequest`,
  `MoneyConverter`, `QuantityConverter` — what a caller asks, and the one core rule that applies a
  rate or factor with its declared rounding.
- **The provider ports and pipelines**: `MoneyRateProvider` and `UnitConversionProvider` — the
  interfaces extensions implement — beside the catalog ports (`MoneyRateProviderCatalog`,
  `UnitConversionProviderCatalog`), the refusal exceptions (`MoneyRateUnavailable`,
  `UnitConversionUnavailable`), and the pipelines (`MoneyConversionPipeline`,
  `UnitConversionPipeline`) that ask each entitled provider in order and refuse an answer that does
  not answer the question.

## What Conversion must never contain

1. **No rate and no conversion table, of any kind.** The App roadmap's decision D10 and ADR 0004
   are explicit: "Core ships no rate table, no rate feed and no rate policy," and its decision
   D13.5 says the same of unit conversion — "core ships no conversion table." A rate provider, a
   factor table, a bank feed, a fixed contractual rate: every one of them is an implementation of a
   port defined here, living in an extension, never in this package.
2. **No SPI wiring.** The registrars, contribution definitions, capability plumbing, and runtime
   catalogs that make a provider reachable inside an installation are the App's authority. This
   package defines what a provider is; the App decides which providers exist, in what order, and
   under what admission bounds.
3. **No storage.** Nothing here owns a database, a table, a column, or a codec into one. Stored
   exact values are the host's; conversion reads them and writes none.
4. **No presentation and no rendering.** How a converted figure is rendered on a screen, in a report
   column, or in an export artifact — the carriage of provenance through those surfaces — stays in
   the App. This package guarantees the provenance exists and cannot be dropped; the App guarantees
   it is shown.
5. **No runtime Composer dependencies.** The package runs on PHP alone, so a host adopts a contract,
   not a dependency tree.

## The non-negotiable rule

Stated by the App roadmap (D10) and carried here verbatim in spirit, because the type system of this
package is its enforcement:

> **A converted amount is always marked as converted and carries its rate and its as-at instant.**
> Everywhere it appears — screen, report, export, API response, event payload. A displayed price
> that silently drifts from its stored value is an audit defect, not a formatting choice.

And its storage half, from the same decision: "Conversion is a presentation and reporting concern
layered **above** stored exact values and never a mutation of them; a converted figure is never
written back into the field it came from."

In this package the rule is not a convention: `ConvertedMoneyValue` and `ConvertedQuantityValue`
have no partial shape. The constructor takes the source value, the presented value, the rate or
factor with its as-at instant and provider, the declared rounding mode, and the unrounded exact
product — and recomputes the product and the rounding, refusing a value whose numbers do not follow
from its own provenance. An encoder cannot drop the fields because no object without them exists.

## Drop-in mechanics

The package-owned public-API manifest pins the `MoneyRateProvider` and `UnitConversionProvider`
interfaces and their complete fifteen-type transitive closure as `extension-provider-v1`. The App's
consumer record stores only the canonical package coordinate, profile identifier, profile digest,
and type count; its gate recomputes that digest from the installed manifest's full type shapes and
loads every selected type under its canonical name. Extraction must not move that surface an inch
in behaviour:

- **Canonical namespace here.** Every extracted type lives under `Kumwe\Conversion\` in this
  repository, which is its one canonical home from the first release onward.
- **Canonical names everywhere.** The canonical `Kumwe\Conversion\` names are the only names. The
  App's adoption change migrates every reference — imports, FQCN strings, and docblocks — to the
  canonical names, deletes its copies and obsolete historical surface records, and records the
  package profile digest and type count. It retires the historical `Kumwe\App\...` names in that
  same change. No compatibility layer, no maintenance surface: nothing resolves a retired name,
  and nothing has to, because no third-party extension was ever published against the historical
  names. The rename record in
  [`docs/app-agreement.md`](docs/app-agreement.md) is the historical record of what moved where.
- **Identity proven, not asserted.** The package owns and preserves the extracted unit corpus. The
  App removes only byte-for-byte duplicate unit coverage and keeps its retained unit, integration,
  functional, and architecture boundary assertions green without rewriting them before adoption is
  claimed. The package manifest is the type-shape authority; the App's minimal digest/count record
  and its independent consumer gate prove that the exact installed release supplies that reviewed
  profile without aliases or a copied second authority.

The protocol both repositories follow is recorded in [`docs/app-agreement.md`](docs/app-agreement.md).

## The boundary in one line

**This package says what a conversion is. Extensions say what the rate is. The App says who may ask.**

## Relationships

- **With Kumwe App** ([`kumwe/app`](https://github.com/kumwe/app)): the App is Conversion's first
  consumer, never its owner. The App pins an exact version, imports the canonical names directly,
  owns the SPI that contributes providers, and owns every surface that renders a converted figure.
  The agreement is recorded in [`docs/app-agreement.md`](docs/app-agreement.md).
- **With extensions**: extensions are the provider implementors. A rate package implements
  `MoneyRateProvider`; a unit-table package implements `UnitConversionProvider`; both reach an
  installation only through the App's contribution SPI and are withdrawn with their package. Their
  compile-time dependency is this package's ports.

## Governance

Work is recorded in [`docs/roadmap.md`](docs/roadmap.md) while open and in `CHANGELOG.md` when
delivered; a claim states only what the check lane proves on a clean clone. The check lane is
`composer check`: Composer metadata/autoload, the frozen public API, lint, style, PHPStan max,
documentation completeness, and the dependency-free behavioural suite. Every commit passes it.
The engineering rules live in
[`docs/engineering-standard.md`](docs/engineering-standard.md).
