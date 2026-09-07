# Conversion remaining work

Only unresolved work belongs here. The implemented 23-type Decimal, Value, Contract and Provider
surface, its package-owned tests and its releases are recorded in [CHANGELOG.md](../CHANGELOG.md)
and the [public API manifest](../resources/public-api/v1.json). The historical source-to-package mapping remains in
[the App agreement](app-agreement.md); it is an adoption record, not an implementation backlog.

## C-4 — Finish consumer test ownership and release verification

Read-only inspection of App commit `24ecf956423c18933e824b43cea1bfb9127a79a9` on 2026-09-07
confirms that its Composer manifest already pins `kumwe/conversion` `0.1.2` and the extracted
production classes use the canonical package. Repeating their extraction is unnecessary.

The remaining consumer work is to verify the selected successor release and remove residual
library-unit ownership. For example, App still contains
`tests/Unit/BusinessRecord/Domain/ExactDecimalTest.php`, which tests the library's decimal
implementation. The complete decimal behavior and language-neutral conformance corpus belong
in this repository. App must retain its persistence, composition, HTTP/CLI, authority and
integration tests, including its Money Conversion, Unit Conversion and Converted Money Surface
architecture boundaries. The next App change must compare the actual tests with this package's
suite before deleting them and verify the combined consumer graph.

The latest observed library release is `0.1.3` at
[`e95d5633722929e77b73005f8c44cfe4d99ac8c3`](https://github.com/kumwe/conversion/releases/tag/v0.1.3).
A released version alone does not prove independent artifact verification or App acceptance.
The integration train must select a coherent set of exact package versions: released dependents
that require `0.1.0` need successor releases before a single Composer graph can move to `0.1.3`.
No App changes are part of this repository's readiness review.

## Ownership of contribution wiring

`kumwe/conversion-extension` owns the portable money-rate and unit-conversion provider
definitions. SDK signed manifest declarations and executable bindings supersede the old
Money Rate Provider and Unit Conversion Provider registrar interfaces. App retains trusted
activation, runtime catalogue adapters, rate/provider policy and persistence. Those responsibilities
must not be copied into Conversion.

Rates, tables, storage, presentation, new rounding modes and unrelated arithmetic extensions remain
outside this extraction. Any new capability requires a specific ownership and compatibility review.
