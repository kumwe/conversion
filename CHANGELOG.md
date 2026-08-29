# Changelog

Delivered package changes, newest first. A change is recorded here only after its stated proof
passes on a clean clone.

## 0.1.2 - 2026-08-29

- **The published API is now executable evidence.** A deterministic package-owned manifest freezes
  the complete reflected shape of all twenty-three canonical public types. CI refuses an added,
  removed, or changed type, constant, property, method, parameter, return, or enum case unless a
  reviewed release deliberately records the new surface. Its `extension-provider-v1` profile owns
  the exact fifteen-type transitive surface provider implementations compile against.
- **The package gate now proves the distributed Composer library.** Strict Composer validation,
  Composer-autoload smoke coverage for every public type, PSR-12, PHPStan level max with strict and
  deprecation rules, syntax/member-documentation checks, and the behavioural suite all run before a
  release. CI repeats the autoload proof after removing every development package and optimizing an
  authoritative production classmap, then builds the actual consumer archive, rejects development-only
  paths, installs it without development dependencies, and loads all twenty-three public types from the
  shipped package-owned manifest. Test discovery now fails when the suite or a discovered case is empty.
- **Release automation is least-authority and target-checked.** Read permission is the workflow
  default, write permission exists only in the release job, and an existing version tag must resolve
  to a release commit in `main` history whose newest changelog record matches the tag.
- **The extraction proof has one owner.** Duplicate unit coverage was consolidated into this
  package while downstream boundary, integration, functional, and architecture assertions remained
  unchanged.

## 0.1.1 - 2026-08-28

- **Canonical package types are consumed directly.** Consumers migrated to the
  `Kumwe\Conversion\` public types and removed their extracted copies.
- **C-5 — Packagist.** `kumwe/conversion` is live on Packagist through the GitHub integration,
  with releasing on the record: the `Release on record` workflow re-proves each newly recorded
  version, tags it, and publishes the GitHub release, and Packagist follows the tags with no
  credential in this repository.

## 0.1.0 - 2026-08-28

- **C-3 — Requests, converters, pipelines, and the ports.** Added `MoneyConversionRequest`,
  `UnitConversionRequest`, `MoneyConverter`, and `QuantityConverter` under
  `Kumwe\Conversion\Contract`, together with provider and catalog ports, typed refusals, and the two
  pipelines under `Kumwe\Conversion\Provider`. The refusal corpus is replayed with in-suite
  scripted catalogs and providers: declared order respected, a declining provider
  passed over unasked, a misattributed, mispriced or postdated answer refused with its cause kept,
  a provider that accepts and then cannot source propagating its typed refusal, an empty catalog
  refusing, and no answer that does not carry its rate or factor, as-at instant, provider identity
  and declared rounding.
- **C-2 — The value types.** `MoneyValue`, `QuantityValue`, the two rounding-mode enums,
  `MoneyExchangeRate`, `UnitConversionFactor`, `ConvertedMoneyValue` and `ConvertedQuantityValue`
  added under `Kumwe\Conversion\Value` with the whole provenance discipline:
  constructor recomputation that makes a figure without provable provenance unconstructible, the
  closed export shapes, and the portable string grammars, byte-for-byte in behaviour. The
  construction, refusal, and round-trip corpus is replayed —
  `detect()` recognising exports and rejecting near-misses included — and the quantity-side refusal
  corpus is mirrored onto the money types so every documented refusal is provoked.
- **C-1 — The ExactDecimal kernel.** Added `ExactDecimal`, `ExactDecimalArithmetic`, and
  `ExactRoundingRule` under `Kumwe\Conversion\Decimal` with the established behaviour: the same
  canonicalising factories, the same canonical form, the same refusal
  conditions, the same `MAXIMUM_PRECISION` of 65, and pure digit-string multiplication and rounding
  with no `bcmath` and no `gmp`. The exact test corpus is replayed in the dependency-free
  suite — boundary and round-trip cases, every rounding rule at the tie, remainder and carry
  boundaries, literal reconstitution — with every documented refusal provoked and asserted.
- **Founding.** Established the package boundary, engineering standard, phased delivery record,
  package skeleton, and the check lane (`composer check`: lint, member documentation, and the
  dependency-free suite), run by continuous integration on every push and pull request.
