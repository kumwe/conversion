# Conversion roadmap

Forward work only; delivered work moves to `CHANGELOG.md` in the change that completes it. A phase
is claimed only when its stated proof passes on a clean clone — `composer check` here, and for C-4
the App's own gates over there. Phases land in order; each is separately reviewable and none mixes
extraction with improvement, because the promise of this package is behavioural identity with the
code it replaces.

## Position

Founding. The charter, the engineering standard, and the check lane exist; `src/` is empty and the
lane proves the tooling itself. Everything below extracts code that today lives in Kumwe App under
`Kumwe\App\BusinessRecord\Domain`, `Kumwe\App\BusinessRecord\Application`, and (deliberately left
behind) `Kumwe\App\Extension\Contribution` — verified against the App working tree on 2026-08-28.

## The extraction inventory

What moves, from where, at what size, with what couplings. Every type below depends only on PHP
core (`InvalidArgumentException`, `Stringable`, `DateTimeImmutable`, `RuntimeException`) and on
other types in this same inventory — no Doctrine, no PSR interfaces, no App services — which is
what makes the extraction mechanical once the seams below are respected.

From `src/BusinessRecord/Domain/` (namespace `Kumwe\App\BusinessRecord\Domain`):

| File | Lines | Moves to layer |
| --- | --- | --- |
| `ExactDecimal.php` | 195 | Decimal |
| `ExactDecimalArithmetic.php` | 247 | Decimal |
| `ExactRoundingRule.php` | 47 | Decimal |
| `MoneyValue.php` | 50 | Value |
| `QuantityValue.php` | 57 | Value |
| `MoneyRoundingMode.php` | 102 | Value |
| `QuantityRoundingMode.php` | 102 | Value |
| `MoneyExchangeRate.php` | 159 | Value |
| `UnitConversionFactor.php` | 171 | Value |
| `ConvertedMoneyValue.php` | 355 | Value |
| `ConvertedQuantityValue.php` | 326 | Value |
| `MoneyConversionRequest.php` | 86 | Contract |
| `UnitConversionRequest.php` | 87 | Contract |
| `MoneyConverter.php` | 61 | Contract |
| `QuantityConverter.php` | 63 | Contract |

From `src/BusinessRecord/Application/` (namespace `Kumwe\App\BusinessRecord\Application`):

| File | Lines | Moves to layer |
| --- | --- | --- |
| `MoneyRateProvider.php` | 65 | Provider |
| `UnitConversionProvider.php` | 66 | Provider |
| `MoneyRateProviderCatalog.php` | 36 | Provider |
| `UnitConversionProviderCatalog.php` | 36 | Provider |
| `MoneyRateUnavailable.php` | 19 | Provider |
| `UnitConversionUnavailable.php` | 20 | Provider |
| `MoneyConversionPipeline.php` | 79 | Provider |
| `UnitConversionPipeline.php` | 83 | Provider |

Twenty-three files, roughly 2,500 lines. Stays in the App, by charter (the SPI seam):
`Extension/Contribution/MoneyRateProviderRegistrar.php`,
`Extension/Contribution/UnitConversionProviderRegistrar.php`,
`Extension/Contribution/UnitConversionProviderDefinition.php`,
`BusinessRecord/Domain/MoneyRateProviderDefinition.php`,
`BusinessRecord/Infrastructure/RuntimeMoneyRateProviderCatalog.php`, and
`BusinessRecord/Infrastructure/RuntimeUnitConversionProviderCatalog.php`.

Two seams need care, recorded here so no phase discovers them mid-flight:

1. **`MoneyRateProviderDefinition` sits in `BusinessRecord\Domain` but is SPI.** It implements
   `Kumwe\App\Extension\Contribution\ContributionDefinition`, so it is contribution plumbing that
   happens to live in the domain folder (its quantity twin, `UnitConversionProviderDefinition`,
   lives in `Extension\Contribution`). It stays in the App with the registrars. After C-4 the App
   keeps one class of its own in a namespace whose other members are aliases — legal, because
   aliases bind per class, never per namespace.
2. **The pipelines require the catalog ports.** `MoneyConversionPipeline` is constructed from
   `MoneyConverter` and `MoneyRateProviderCatalog`, so the catalog *interfaces* travel with the
   package (they are how discovery is injected), while the App keeps its registry-reading
   implementations. The catalog docblocks say "core's own implementation reads the extension
   contribution registry" — after extraction that sentence describes the App's implementations,
   and the ported blocks are reworded to say so without changing any signature.

## Phases

### C-1 — The ExactDecimal kernel

Extract `ExactDecimal`, `ExactDecimalArithmetic`, and `ExactRoundingRule` into
`Kumwe\Conversion\Decimal`, byte-for-byte in behaviour: same factories, same canonical form, same
refusal conditions, same `MAXIMUM_PRECISION` of 65, pure digit-string arithmetic with no `bcmath`
and no `gmp`.

**Proof.** The App's exact test corpus replayed here in the dependency-free suite: the cases of
`tests/Unit/BusinessRecord/Domain/ExactDecimalTest.php` plus every arithmetic and rounding case
embedded in the App's `MoneyConversionContractTest` and `UnitConversionContractTest` (canonical
form, negative zero, padding, `multiply` exactness, every rounding mode at every boundary digit,
`fromLiteral` refusals). Determinism: identical bytes across two runs. `composer check` green.

**Non-goals.** No new arithmetic — the kernel ships exactly what the App ships (`multiply`,
`round`, `fromLiteral`, the canonicalising factories) and nothing speculative such as addition or
division; drop-in, not mutation. No float bridge in either direction. No storage codec —
`RecordValueCodec` and `RecordValueGuard` are App write-path authority and never move.

### C-2 — The value types

Extract the money and quantity vocabulary into `Kumwe\Conversion\Value`: `MoneyValue`,
`QuantityValue`, the rounding-mode enums, `MoneyExchangeRate`, `UnitConversionFactor`, and the two
converted value types with their whole provenance discipline — constructor recomputation, the
closed export shapes, and the portable string grammars.

**Proof.** Construction and refusal tests for every type: what each constructor admits, and each
documented `InvalidArgumentException` provoked and asserted. Unconstructibility proofs for
`ConvertedMoneyValue` and `ConvertedQuantityValue` — a wrong-pair rate, a product that is not the
exact product, a rounding that does not follow from the declared mode, each refused. Round trips
byte-identical: `toArray`/`fromArray` and `toPortableString`/`fromPortableString`, replaying the
value-shape cases of the App's `MoneyConversionContractTest` and `UnitConversionContractTest`,
including `detect()` recognising exports and rejecting near-misses.

**Non-goals.** No presentation: locale formatting of a figure is ICU territory in the App
(its ADR 0002) and never enters this package. No rate data: test vectors carry rates and factors
as inputs of a proof, never as a shipped table, default, or lookup. No widening of any export
shape — a byte added to `toArray()` output is an App-facing surface change, not extraction.

### C-3 — Requests, converters, pipelines, and the ports

Extract `MoneyConversionRequest`, `UnitConversionRequest`, `MoneyConverter`, and
`QuantityConverter` into `Kumwe\Conversion\Contract`; extract the ports and pipelines —
`MoneyRateProvider`, `UnitConversionProvider`, the two catalog ports, the two refusal exceptions,
and the two pipelines — into `Kumwe\Conversion\Provider`.

**Proof.** Provenance-carriage tests: the only type either pipeline can return is a converted
value, and the suite proves the pipeline cannot emit a figure without rate or factor, as-at
instant, provider identity, and declared rounding, because no other return shape exists. The App's
refusal corpus replayed: a rate attributed to another provider, a rate pricing another pair, a
rate postdating the instant asked about, a provider that accepts then throws, and no entitled
provider at all — each raising `MoneyRateUnavailable`/`UnitConversionUnavailable` with the App's
conditions (cases from `UnitConversionRefusalTest`, `UnitConversionPipelineRefusalTest`, and the
contract halves of `MoneyRateProviderContributionTest`). Catalog injection proven with in-suite
fake catalogs: declared order respected, `supports()` false moves on, empty catalog refuses.

**Non-goals.** No catalog implementation — `RuntimeMoneyRateProviderCatalog` and
`RuntimeUnitConversionProviderCatalog` read the App's extension contribution registry and stay
there. No registrars and no definition types (the SPI seam above). No admission policy: which
providers are entitled, in what order, under what declared-currency bounds, is the catalog
implementor's authority. No triangulation, base-currency routing, or rate policy of any kind.

### C-4 — The App consumes the package

The App requires `kumwe/conversion` at an exact pin, deletes its twenty-three extracted files, and
adds a `class_alias` shim for every previously published `Kumwe\App\...` FQCN in the inventory —
the eight pinned extension-API names first among them — resolving to the canonical
`Kumwe\Conversion\` classes. The complete alias table is normative in
[`app-agreement.md`](app-agreement.md).

**Proof.** The App's full existing suite green **unchanged**: unit, integration, functional, and
the architecture boundary tests (`MoneyConversionBoundaryTest`, `UnitConversionBoundaryTest`,
`ConvertedMoneySurfaceCoverageTest`). The compatibility fixtures
`tests/Fixtures/ExtensionApi/money-rate-provider-v1.json` and
`tests/Fixtures/ExtensionApi/unit-conversion-provider-v1.json` byte-identical — they are the
pinned surface, and the aliases exist precisely so those FQCNs keep resolving. `composer
extension:contract` green with zero fixture edits; the App's baseline re-recorded only for the
file-inventory moves its own process requires. Behavioural identity, not equivalence: any App test
that needs editing to pass is a defect in the extraction, not in the test.

**Non-goals.** No behaviour change rides along, however small. No migration of App call sites off
the aliased names — internal callers may keep the old imports; renaming them is later App
housekeeping on its own schedule. No changes to the App's SPI, capability plumbing, rendering, or
`undeclared_currency_conversions` qualification objective.

### C-5 — Packagist

Publish `kumwe/conversion` on Packagist through the GitHub integration, with a releasing document
in the family pattern: tag `vX.Y.Z`, the release workflow re-proves the tagged commit, the
changelog must record the version. The App moves its pin from a repository source to the published
release.

**Proof.** Clean-room install: `composer require kumwe/conversion` into an empty project resolves
the published package with no extra dependencies, and `composer check` passes on the tagged
commit. The App's suite green on the published pin.

**Non-goals.** No `1.0.0` claim before C-4 has held through at least one App release cycle; while
the package is `0.x` the App and extensions pin exactly.

## Deferred by design

Anything the charter forbids — rates, tables, SPI wiring, storage, presentation — and any new
capability (new arithmetic, new rounding modes, new provenance fields). New capability starts as a
finding here, agreed with the App under the pin protocol, after extraction is complete.
