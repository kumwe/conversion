# Changelog

Delivered work only, newest first; open work lives in [`docs/roadmap.md`](docs/roadmap.md). A phase
is recorded here in the change that completes it, when its stated proof passes on a clean clone.

## Unreleased

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
