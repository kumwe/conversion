# Decimal conformance profile v1

`resources/conformance/decimal-v1.tsv` is the package-owned, language-neutral corpus for the existing exact-decimal contract. All 108 vectors are replayed by `DecimalConformanceTest`; no App runtime or database is required. It introduces no arithmetic operation or rounding policy.

The UTF-8 TSV has one header and nine columns: `id`, `operation`, `left_hex`, `right_hex`, `precision`, `scale`, `rounding`, `outcome`, `expected_hex`. A final LF is required. Operand/result bytes are lower-case hexadecimal (an empty field means an empty byte string). A dash denotes an unused field, not an operand. Integers use base ten. IDs are unique. Outcomes are `value` or `invalid_argument`; the latter requires refusal and carries no result. PHP exception wording is not a cross-language contract.

- `parse`: `ExactDecimal::fromString(left, precision, scale)`. Output is its canonical fixed-scale literal, including zero normalization and padding.
- `multiply`: both operands use `ExactDecimalArithmetic::fromLiteral`, then `multiply`. Output precision is 65 and scale is the sum; overflow refuses.
- `compare`: both operands use `fromLiteral`, then compare at equal scale. Output normalizes comparison sign to `-1`, `0`, or `1`. Different scales refuse.
- `round`: left uses `fromLiteral`, then rounds to the target precision and scale using the named `half_up`, `half_down`, `half_even`, `ceiling`, `floor`, or `truncate` rule. Money and quantity rules replay the same answers. Overflow refuses.

Expected multiplication and rounding values were calculated with independent integer coefficient arithmetic, then reviewed at tie, carry, sign, zero and portable-width boundaries. They are committed constants; the test does not generate expected answers from the implementation under test. The corpus is additive evidence rather than a proof of all input combinations. Existing value, request, provider and pipeline tests remain required.

Consumers pin the corpus bytes and the exact released Conversion version. A source branch, a matching local result, or these vectors alone is not independent release verification and cannot authorize dependent publication. Engine may use this as reviewed draft semantic evidence until the release protocol is complete. App retains storage codecs, provider admission/lifecycle, authorization and provenance across rendered/exported surfaces.
