# Conversion roadmap

Forward work only; delivered work moves to `CHANGELOG.md` in the change that completes it. A phase
is claimed only when its stated proof passes on a clean clone — `composer check` here, and for C-4
the App's own gates over there. Phases land in order; each is separately reviewable and none mixes
extraction with improvement, because the promise of this package is behavioural identity with the
code it replaces.

## Position

Extracted and published. The whole conversion contract — the Decimal, Value, Contract and Provider
layers (C-1 to C-3) — and the Packagist publication with its release automation (C-5) are
delivered and recorded in [`CHANGELOG.md`](../CHANGELOG.md). One phase remains: the App consumes
the package directly at the canonical names (C-4). The inventory stays, in the past tense,
because it is the normative file list for the App's adoption (C-4): what moved out of
`Kumwe\App\BusinessRecord\Domain` and `Kumwe\App\BusinessRecord\Application`, and what
(deliberately) stayed behind — verified against the App working tree on 2026-08-28.

## The extraction inventory

What moved, from where, at what size, with what couplings. Every type below depends only on PHP
core (`InvalidArgumentException`, `Stringable`, `DateTimeImmutable`, `RuntimeException`) and on
other types in this same inventory — no Doctrine, no PSR interfaces, no App services — which is
what makes the extraction mechanical once the seams below are respected.

From `src/BusinessRecord/Domain/` (namespace `Kumwe\App\BusinessRecord\Domain`):

| File | Lines | Layer |
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

| File | Lines | Layer |
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
   lives in `Extension\Contribution`). It stays in the App with the registrars.
2. **The pipelines require the catalog ports.** `MoneyConversionPipeline` is constructed from
   `MoneyConverter` and `MoneyRateProviderCatalog`, so the catalog *interfaces* travel with the
   package (they are how discovery is injected), while the App keeps its registry-reading
   implementations. The catalog docblocks say "core's own implementation reads the extension
   contribution registry" — after extraction that sentence describes the App's implementations,
   and the ported blocks are reworded to say so without changing any signature.

## Phases

### C-4 — The App consumes the package

The App requires `kumwe/conversion` at an exact pin from Packagist, deletes its twenty-three
extracted files, and migrates every reference — imports, FQCN strings, and docblocks — to the
canonical `Kumwe\Conversion\` names, the fifteen types in the package-owned
`extension-provider-v1` profile first among them. It removes obsolete historical surface records
and adds one minimal consumer record containing the package coordinate, profile identifier, digest,
and type count. The historical `Kumwe\App\...` names are retired in that same change; no
compatibility layer and no maintenance surface replace them, because no third-party extension was
ever published against the historical names. The rename record is normative in
[`app-agreement.md`](app-agreement.md).

**Proof.** The package-owned behavioural corpus remains green without rewritten assertions. The App
deletes only the unit tests that are byte-for-byte duplicates of that package corpus, while its
retained unit, integration, functional, and architecture boundary tests
(`MoneyConversionBoundaryTest`, `UnitConversionBoundaryTest`,
`ConvertedMoneySurfaceCoverageTest`) remain green without changing their behavioural assertions.
The App's `conversion-api-profile.json` contains only the package coordinate, profile identifier,
reviewed digest, and type count. `composer conversion:api` independently rebuilds that digest from
the installed package manifest's full type shapes, verifies the ordered fifteen-type closure, and
autoloads every member under its canonical name without aliases. The exact-dependency gate binds
the release to its official forty-character Composer lock reference. The App's baseline is
re-recorded only for the file-inventory moves its own process requires. Behavioural identity, not
equivalence: any retained App assertion that needs editing to pass is a defect in the adoption, not
in the test.

**Non-goals.** No behaviour change rides along, however small. Retiring every historical reference
is in scope, not later housekeeping: no internal caller keeps an old import, and no
`Kumwe\App\...` conversion name survives the change. No changes to the App's SPI, capability
plumbing, rendering, or `undeclared_currency_conversions` qualification objective. No `1.0.0`
claim before this phase has held through at least one App release cycle; while the package is
`0.x` the App and extensions pin exactly.

## Deferred by design

Anything the charter forbids — rates, tables, SPI wiring, storage, presentation — and any new
capability (new arithmetic, new rounding modes, new provenance fields). New capability starts as a
finding here, agreed with the App under the pin protocol, after extraction is complete.
