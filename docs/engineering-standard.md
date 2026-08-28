# Engineering standard

This document says out loud what "good" means in this repository, so quality is a stated, checkable
expectation rather than a hope. It binds every contribution, human or agent. The
[charter](../CHARTER.md) says what Conversion is; this says how it is built. It descends from the
Kumwe App coding standard and the Producer engineering standard, adapted to a package whose whole
subject is exactness and provenance.

## Architecture

Conversion is a layered library with one dependency direction. A layer may use the layers above it
in this table and never the ones below:

| Layer | Namespace | Owns | Must never know about |
| --- | --- | --- | --- |
| Decimal | `Kumwe\Conversion\Decimal` | Exact values, exact arithmetic, the rounding rule | money, quantities, providers, hosts |
| Value | `Kumwe\Conversion\Value` | Money/quantity values, rates, factors, rounding modes, converted values | providers, pipelines, hosts |
| Contract | `Kumwe\Conversion\Contract` | Conversion requests and the core converters | provider discovery, hosts |
| Provider | `Kumwe\Conversion\Provider` | Provider and catalog ports, refusals, pipelines | any concrete provider, registry, or host |

Rules that keep the layers honest:

1. **The extracted behaviour is the API.** This package is a drop-in replacement for code the App
   already ships and has pinned. A public shape here matches the shape the App published — the same
   constructors, the same refusal messages' conditions, the same export arrays, the same portable
   strings, byte for byte. Improvements that move behaviour are new work with a new version, never
   part of extraction.
2. **All discovery is injected.** The package never decides which providers exist. Candidates arrive
   through the catalog ports; the App composes them. There is no service locator, no global state,
   no static mutable anything.
3. **Fail closed, with typed refusals.** A request the contract cannot answer raises the named
   exception (`MoneyRateUnavailable`, `UnitConversionUnavailable`) or `InvalidArgumentException` at
   construction. A provider's answer is never taken on trust: a rate attributed to another provider,
   pricing another pair, or postdating the instant asked about is refused, not converted with.
4. **Determinism.** Same input, same Conversion release: identical output bytes. No clocks, no
   randomness, no locale-dependent formatting anywhere in the library. The as-at instant is part of
   the input, supplied by the caller, never read from the environment.
5. **Bounded before expensive.** Precision, scale, and identifier bounds are enforced before
   arithmetic or parsing walks attacker-influenced data.

## The exact-arithmetic discipline

This section is the reason the package exists, and it is absolute.

1. **No floats in money or quantity paths, ever.** An amount, a rate, a factor, a product, and a
   rounded result exist as `ExactDecimal` — a canonical string with a declared precision and scale —
   from construction to export. A `float` parameter, property, return type, cast, or intermediate in
   any value or conversion path is a defect, whatever its tests say. There is no "small enough"
   exception.
2. **Exact arithmetic is digit arithmetic.** `ExactDecimalArithmetic` multiplies and rounds by
   integer digit manipulation on strings. It requires no extension — not `bcmath`, not `gmp` — and
   must not grow a dependency on one, because behaviour would then vary with a host's build.
3. **Deterministic rounding, declared not implied.** Rounding never happens implicitly. A conversion
   request declares its target precision, scale, and rounding mode; the converted value carries the
   mode and the unrounded exact product it was applied to, so any reader can reproduce the rounding
   from the value's own evidence. A new rounding mode is a new enum case implementing
   `ExactRoundingRule`, described by the four facts every rounding rule is expressed in — never an
   arithmetic special case.
4. **Canonical form is the only form.** Construction goes through the factories, so every instance
   is already canonical: padded to its scale, free of insignificant leading zeros, negative zero
   spelled as zero. That is what lets a value be stored, checksummed, and compared as a plain
   string, and what makes "byte for byte" a meaningful promise.

## Code

- `declare(strict_types=1)` in every file; `final` classes by default and `final readonly` wherever
  the instance carries no mutable state; constructor property promotion for wiring; native types on
  every parameter, return, and property; small classes named for their one responsibility.
- No runtime Composer dependencies, ever (`php` alone). This is a contract a host adopts, not a
  dependency tree.
- One class-like declaration per file, named after the file, autoloaded PSR-4 from
  `Kumwe\Conversion\`. Code lines stay at or below 120 characters; documentation prose wraps at
  roughly 100.
- Exception messages are complete sentences addressed to an operator and never contain secrets or
  raw payloads.

## Documentation blocks

Every documentable member — class-like declaration, method, non-promoted property, class constant,
and enum case — carries a documentation block, enforced by `php tools/check-docblocks.php` in
`composer check` and CI. The format is the Kumwe App standard, restated here in short:

1. **A summary sentence** stating what the member does, then optionally a paragraph on when to reach
   for it — the guarantee it makes and which collaborator owns the parts it does not. A block that
   restates the identifier ("Gets the name.") is noise and a defect.
2. **Aligned, ordered tags**: `@param` entries in signature order (promoted constructor properties
   included), then `@return` (except constructors), then `@throws` with the condition each entry is
   raised under, then the trailing group. Within a block, tag values start in one column: two spaces
   after the longest tag name in the block.
3. **`@since` is always last, and always present.** It records the version that introduced the
   member and is never rewritten. Members created by the extraction carry the App-published
   behaviour's history in prose where it matters, and this package's own introducing version in
   `@since`.
4. **Types are precise.** `list<string>`, `array<string, mixed>`, and shaped arrays like
   `array{amount: string, currency: string}` — never a bare `array`. Never widen or delete an
   existing documented type.

## Testing

The suite exists to prove intended outcomes, and only that. The standard for every test:

1. **A test asserts an observable contract, not an implementation detail** — the canonical string, a
   refusal and its condition, the export array, the portable round trip — never that a private
   method was called.
2. **Every value type is proven by construction and refusal tests.** What the constructor admits and
   the exact conditions under which it throws are both first-class. A refusal nothing has provoked
   is a branch nothing has tested; a value type whose refusals are untested is untested.
3. **Every pipeline is proven by provenance-carriage tests.** The suite demonstrates that a
   converted value cannot be produced without its rate or factor, its as-at instant, its provider
   identity, and its declared rounding; that the pipeline refuses a misattributed, mispriced, or
   postdated answer; and that provenance survives every published round trip (`toArray` /
   `fromArray`, portable string in and out) byte for byte.
4. **The App's exact corpus is the spine of extraction.** Behaviour the App already proves is
   re-proven here by replaying the equivalent cases, so a divergence from the code being replaced is
   a red suite, not a review comment.
5. **No frivolous tests.** A test that cannot fail for a reason a user would care about — a getter
   returning what the constructor took, asserting a class exists — must not be written. Coverage is
   a consequence of testing outcomes, never a goal pursued for its own number.
6. **Dependency-free and deterministic.** Tests run with `php tests/run.php` on a clean clone with
   no composer install, touch no network and no clock, and pass in any order.

## Documentation

- Every document states behaviour that exists; plans live in [`docs/roadmap.md`](roadmap.md) and
  nowhere else. A claim the check lane cannot back is a defect in the document.
- Wrap prose at roughly 100 columns; sentence-case headings; link with relative paths; write for
  the reader who arrives with no context, because the next implementer may be an agent with nothing
  but this repository.

## The check lane

`composer check` is the whole standard, executable: lint, documentation completeness, suite. Every
commit passes it — including the founding state, where `src/` is empty and the lane proves the
tooling itself. CI runs it on every supported PHP version; a release re-proves it on the tagged
commit. There is no path to publication that skips it.
