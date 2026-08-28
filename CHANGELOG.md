# Changelog

Delivered work only, newest first; open work lives in [`docs/roadmap.md`](docs/roadmap.md). A phase
is recorded here in the change that completes it, when its stated proof passes on a clean clone.

## Unreleased

- **C-1 — The ExactDecimal kernel.** `ExactDecimal`, `ExactDecimalArithmetic` and
  `ExactRoundingRule` extracted from Kumwe App into `Kumwe\Conversion\Decimal`, byte-for-byte in
  behaviour: the same canonicalising factories, the same canonical form, the same refusal
  conditions, the same `MAXIMUM_PRECISION` of 65, and pure digit-string multiplication and rounding
  with no `bcmath` and no `gmp`. The App's exact test corpus is replayed in the dependency-free
  suite — boundary and round-trip cases, every rounding rule at the tie, remainder and carry
  boundaries, literal reconstitution — with every documented refusal provoked and asserted.
