# Changelog

Delivered work only, newest first; open work lives in [`docs/roadmap.md`](docs/roadmap.md). A phase
is recorded here in the change that completes it, when its stated proof passes on a clean clone.

## 0.1.2 - 2026-08-29

- **The published API is now executable evidence.** A deterministic package-owned manifest freezes
  the complete reflected shape of all twenty-three canonical public types. CI refuses an added,
  removed, or changed type, constant, property, method, parameter, return, or enum case unless a
  reviewed release deliberately records the new surface. Its `extension-provider-v1` profile owns
  the exact fifteen-type transitive provider surface so App can pin its digest without duplicating
  class shapes; no alias or historical namespace is introduced.
- **The package gate now proves the distributed Composer library.** Strict Composer validation,
  Composer-autoload smoke coverage for every public type, PSR-12, PHPStan level max with strict and
  deprecation rules, syntax/member-documentation checks, and the behavioural suite all run before a
  release. CI repeats the autoload proof after removing every development package and optimizing an
  authoritative production classmap. Test discovery now fails when the suite or a discovered case is
  empty.
- **Release automation is least-authority and target-checked.** Read permission is the workflow
  default, write permission exists only in the release job, and an existing version tag must resolve
  to a release commit in `main` history whose newest changelog record matches the tag.
- **C-4 proof wording matches the extraction.** Package-owned duplicate unit coverage moves out of
  the App; the App's retained boundary, integration, functional, and architecture assertions remain
  unchanged. The canonical separation still has no aliases, remapping, or compatibility layer.

## 0.1.1 - 2026-08-28

- **Direct consumption replaces the planned alias layer.** The canonical `Kumwe\Conversion\` names
  are the only names, by decision: the App's adoption change (C-4) migrates every reference —
  imports, FQCN strings, docblocks, its classification and its compatibility-fixture records — to
  the canonical names, deletes its copies, and retires the historical `Kumwe\App\...` names in
  that same change, re-recording the pinned fixture records at the canonical names as a one-time,
  deliberate generation action — legitimate because no third-party extension was ever published
  against the historical names. No `class_alias`, no compatibility layer, no maintenance surface.
  The App agreement now carries the rename record — the 23-FQCN table kept as the historical
  record of what moved where — and its exact-pin protocol applies the never-rewritten rule to the
  canonical-name records from the adoption generation onward.
- **C-5 — Packagist.** `kumwe/conversion` is live on Packagist through the GitHub integration,
  with releasing on the record: the `Release on record` workflow re-proves each newly recorded
  version, tags it, and publishes the GitHub release, and Packagist follows the tags with no
  credential in this repository.

## 0.1.0 - 2026-08-28

- **C-3 — Requests, converters, pipelines, and the ports.** `MoneyConversionRequest`,
  `UnitConversionRequest`, `MoneyConverter` and `QuantityConverter` extracted from Kumwe App into
  `Kumwe\Conversion\Contract`; the provider and catalog ports, the refusal exceptions and the two
  pipelines into `Kumwe\Conversion\Provider` — byte-for-byte in behaviour, with the catalog
  docblocks reworded to describe the port neutrally (a host supplies the catalog; the Kumwe App
  implementation reads its extension contribution registry). The App's refusal corpus is replayed
  with in-suite scripted catalogs and providers: declared order respected, a declining provider
  passed over unasked, a misattributed, mispriced or postdated answer refused with its cause kept,
  a provider that accepts and then cannot source propagating its typed refusal, an empty catalog
  refusing, and no answer that does not carry its rate or factor, as-at instant, provider identity
  and declared rounding.
- **C-2 — The value types.** `MoneyValue`, `QuantityValue`, the two rounding-mode enums,
  `MoneyExchangeRate`, `UnitConversionFactor`, `ConvertedMoneyValue` and `ConvertedQuantityValue`
  extracted from Kumwe App into `Kumwe\Conversion\Value` with the whole provenance discipline:
  constructor recomputation that makes a figure without provable provenance unconstructible, the
  closed export shapes, and the portable string grammars, byte-for-byte in behaviour. The
  construction, refusal and round-trip corpus of the App's contract and refusal tests is replayed —
  `detect()` recognising exports and rejecting near-misses included — and the quantity-side refusal
  corpus is mirrored onto the money types so every documented refusal is provoked.
- **C-1 — The ExactDecimal kernel.** `ExactDecimal`, `ExactDecimalArithmetic` and
  `ExactRoundingRule` extracted from Kumwe App into `Kumwe\Conversion\Decimal`, byte-for-byte in
  behaviour: the same canonicalising factories, the same canonical form, the same refusal
  conditions, the same `MAXIMUM_PRECISION` of 65, and pure digit-string multiplication and rounding
  with no `bcmath` and no `gmp`. The App's exact test corpus is replayed in the dependency-free
  suite — boundary and round-trip cases, every rounding rule at the tie, remainder and carry
  boundaries, literal reconstitution — with every documented refusal provoked and asserted.
- **Founding.** The charter with its never-list, the engineering standard inherited from the App,
  the App agreement carrying the exact-pin protocol and the 23-FQCN record of canonical against
  historical names, the phased roadmap where every phase names its proof, the package skeleton,
  and the check lane (`composer check`: lint, the member documentation gate, the dependency-free
  suite), now run by continuous integration on every push and pull request.
