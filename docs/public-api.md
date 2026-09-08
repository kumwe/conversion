# Conversion public API

Every stable type and declared member below is generated from the checked API manifest and source PHPDoc.
See [architecture](architecture.md) for layering, host-owned catalogs, lifetimes and side effects.
The canonical decimal corpus defines exact cross-language output/refusal behavior; rates and rounding are inputs.

## Kumwe\Conversion\Contract\MoneyConversionRequest

What a caller asks for when it wants a stored amount presented in another currency.

The request is the half of the conversion contract a rate provider reads. It names the amount, the
currency it is to be shown in, the instant the rate must be as at, and — because rounding is a
declared step and never an accident — the exact shape the answer is to be rounded to. A provider
decides which rate answers this; it never decides what the answer looks like.

Nothing here reaches storage. A request is built above stored exact values, is satisfied for one
presentation or one report row, and is discarded.

@since  0.1.0

Kind: `class`; source: `src/Contract/MoneyConversionRequest.php`.

### Properties

```json
{
    "amount": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyValue"
    },
    "asAt": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "DateTimeImmutable"
    },
    "precision": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    },
    "rounding": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyRoundingMode"
    },
    "scale": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    },
    "targetCurrency": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    }
}
```

### __construct

```text
State the amount, the target denomination, the as-at instant, and the rounding to apply.

@param   MoneyValue         $amount          Stored amount being presented, with the currency it is held in.
@param   string             $targetCurrency  Uppercase ISO 4217 code the amount is to be shown in.
@param   DateTimeImmutable  $asAt            Instant the rate must be as at, spelled in UTC; a provider may
         answer with an earlier rate but never with a later one.
@param   int                $precision       Total digit budget the converted amount is expressed at, 1 to 65.
@param   int                $scale           Fractional digits the target currency keeps, 0 to $precision.
@param   MoneyRoundingMode  $rounding        Declared rule for the digits the target scale discards.

@throws  InvalidArgumentException  When the target currency is not an ISO 4217 code or is the currency the
         amount is already held in, the instant is not UTC, or precision and scale fall outside the
         portable exact range.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "amount",
            "type": "Kumwe\\Conversion\\Value\\MoneyValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "targetCurrency",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "asAt",
            "type": "DateTimeImmutable",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "precision",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "scale",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rounding",
            "type": "Kumwe\\Conversion\\Value\\MoneyRoundingMode",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### answeredBy

```text
Whether one rate is the right shape and vintage to answer this request.

A provider that offers a rate for the wrong pair, or one dated after the instant asked about, has
answered a different question. Checking it here keeps the rule in one place rather than in every
provider, and keeps a late rate from being presented as though it were the historical one.

@param   MoneyExchangeRate  $rate  Rate a provider offered for this request.

@return  bool  True when the rate prices this pair and is as at this instant or earlier.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "rate",
            "type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Contract\MoneyConverter

The one rule that turns a request and a rate into a converted amount.

Core owns the arithmetic so that two extensions converting the same amount with the same rate produce
the same figure and the same provenance. The rule is short and deliberately has no discretion in it:
multiply exactly, round once by the mode the request declared, and hand back a value that carries the
unrounded product beside the rounded one. Choosing the rate is the provider's decision and choosing
the rounding is the request's; neither is made here.

The converter reads stored values and writes none. It holds no state and no collaborator, which is
what lets a domain object and an application service use the same instance of it.

@since  0.1.0

Kind: `class`; source: `src/Contract/MoneyConverter.php`.

### convert

```text
Apply one rate to one request and produce the fully evidenced result.

@param   MoneyConversionRequest  $request  Amount, target currency, as-at instant and declared rounding.
@param   MoneyExchangeRate       $rate     Rate offered for that request by a rate provider.

@return  ConvertedMoneyValue  The presented figure, marked as converted and carrying its whole provenance.

@throws  InvalidArgumentException  When the rate prices another pair, is dated after the instant asked
         about, or the exact product is wider than a portable exact value can hold.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rate",
            "type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedMoneyValue"
}
```

## Kumwe\Conversion\Contract\QuantityConverter

The one rule that turns a request and a factor into a converted quantity.

Core owns the arithmetic so that two extensions converting the same quantity with the same factor
produce the same figure and the same provenance — which is exactly the disagreement about what a case
of a product is that owning the contract exists to prevent. The rule is short and deliberately has no
discretion in it: multiply exactly, round once by the mode the request declared, and hand back a value
that carries the unrounded product beside the rounded one. Choosing the factor is the provider's
decision and choosing the rounding is the request's; neither is made here.

The converter reads stored values and writes none. It holds no state and no collaborator, which is
what lets a domain object and an application service use the same instance of it.

@since  0.1.0

Kind: `class`; source: `src/Contract/QuantityConverter.php`.

### convert

```text
Apply one factor to one request and produce the fully evidenced result.

@param   UnitConversionRequest  $request  Quantity, target unit, as-at instant and declared rounding.
@param   UnitConversionFactor   $factor   Factor offered for that request by a conversion provider.

@return  ConvertedQuantityValue  The expressed figure, marked as converted and carrying its whole
         provenance.

@throws  InvalidArgumentException  When the factor relates another pair of units, is dated after the
         instant asked about, or the exact product is wider than a portable exact value can hold.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "factor",
            "type": "Kumwe\\Conversion\\Value\\UnitConversionFactor",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedQuantityValue"
}
```

## Kumwe\Conversion\Contract\UnitConversionRequest

What a caller asks for when it wants a stored quantity expressed in another unit of measure.

The request is the half of the conversion contract a table owner reads. It names the quantity, the
unit it is to be expressed in, the instant the factor must be as at, and — because rounding is a
declared step and never an accident — the exact shape the answer is to be rounded to. A provider
decides which factor answers this; it never decides what the answer looks like.

Nothing here reaches storage. A request is built above stored exact values, is satisfied for one
presentation, one picking list or one report row, and is discarded.

@since  0.1.0

Kind: `class`; source: `src/Contract/UnitConversionRequest.php`.

### Properties

```json
{
    "asAt": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "DateTimeImmutable"
    },
    "precision": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    },
    "quantity": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\QuantityValue"
    },
    "rounding": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\QuantityRoundingMode"
    },
    "scale": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    },
    "targetUnit": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    }
}
```

### __construct

```text
State the quantity, the target unit, the as-at instant, and the rounding to apply.

@param   QuantityValue         $quantity    Stored quantity being expressed, with the unit it is held in.
@param   string                $targetUnit  Portable identifier of the unit the quantity is to be shown in.
@param   DateTimeImmutable     $asAt        Instant the factor must be as at, spelled in UTC; a provider may
         answer with an earlier factor but never with a later one.
@param   int                   $precision   Total digit budget the converted quantity is expressed at, 1 to 65.
@param   int                   $scale       Fractional digits the target unit keeps, 0 to $precision.
@param   QuantityRoundingMode  $rounding    Declared rule for the digits the target scale discards.

@throws  InvalidArgumentException  When the target unit is not a bounded portable identifier or is the unit
         the quantity is already held in, the instant is not UTC, or precision and scale fall outside the
         portable exact range.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "quantity",
            "type": "Kumwe\\Conversion\\Value\\QuantityValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "targetUnit",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "asAt",
            "type": "DateTimeImmutable",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "precision",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "scale",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rounding",
            "type": "Kumwe\\Conversion\\Value\\QuantityRoundingMode",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### answeredBy

```text
Whether one factor is the right shape and vintage to answer this request.

A provider that offers a factor for the wrong pair of units, or one dated after the instant asked
about, has answered a different question. Checking it here keeps the rule in one place rather than
in every provider, and keeps last week's case size from being presented as though it were the one
that applied when the document was raised.

@param   UnitConversionFactor  $factor  Factor a provider offered for this request.

@return  bool  True when the factor relates this pair and is as at this instant or earlier.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "factor",
            "type": "Kumwe\\Conversion\\Value\\UnitConversionFactor",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Decimal\ExactDecimal

A fixed-scale base-10 value. PHP floats are deliberately outside this contract.

Decimal, money and quantity fields exist as this string-backed type from the moment a host's value
codec accepts them until they reach their storage column, because a float would lose digits the
field promised to keep — a host's write path rejects floats in record values outright.
Construction goes through the factories only, so every instance is already canonical: padded to the
field scale, free of insignificant leading zeros, and with negative zero spelled as zero. That is
what lets `value()` be stored, checksummed and compared as a plain string.

@since  0.1.0

Kind: `class`; source: `src/Decimal/ExactDecimal.php`.

### Properties

```json
{
    "precision": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    },
    "scale": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "int"
    }
}
```

### __toString

```text
Render the canonical literal wherever a string is expected, such as query binding or logging.

@return  string  The same text `value()` returns.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

### compare

```text
Order this value against another taken from the same field.

Comparison walks digits rather than converting to a number, so it stays exact well past the
range a PHP integer or float could hold. Equal scale is required rather than coerced: two
decimals of different scale come from different fields, and quietly aligning them would hide
that mismatch instead of surfacing it.

@param   self  $other  Value from the same field, and therefore of the same scale.

@return  int  Negative, zero or positive as this value sorts before, with, or after $other.

@throws  InvalidArgumentException  When $other carries a different scale.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "other",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "int"
}
```

### fromInt

```text
Lift a whole number into a field's precision and scale.

@param   int  $value      Whole number to store; its fraction is filled with zeros.
@param   int  $precision  Total digits the field allows, 1 to 65.
@param   int  $scale      Fractional digits the field stores, 0 to $precision.

@return  self  The canonical value, padded to $scale fractional digits.

@throws  InvalidArgumentException  When precision or scale falls outside the portable database
         range, or the number needs more integer digits than the field leaves for them.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "precision",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "scale",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
}
```

### fromString

```text
Canonicalise a base-10 literal against the precision and scale of the field storing it.

Nothing is repaired silently: exponent notation, a leading `+`, insignificant leading zeros and
a fraction wider than the field scale are all rejected rather than rounded or trimmed, so a
caller never gets back a number it did not supply. A shorter fraction is padded, since the
canonical form always carries exactly $scale fractional digits.

@param   string  $value      Signed base-10 literal such as `-12.50`, without exponent or separators.
@param   int     $precision  Total digits the field allows, 1 to 65.
@param   int     $scale      Fractional digits the field stores, 0 to $precision.

@return  self  The canonical value, padded to $scale fractional digits.

@throws  InvalidArgumentException  When precision or scale falls outside the portable database
         range, the literal is not canonical base-10, its fraction is wider than the scale, or
         its integer digits exceed the precision left over after the scale.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "precision",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "scale",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
}
```

### value

```text
Return the canonical literal that a host writes to storage and folds into its checksums.

@return  string  Plain base-10 form carrying exactly the field's scale in fractional digits.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

## Kumwe\Conversion\Decimal\ExactDecimalArithmetic

Exact base-10 multiplication and rounding for the money and quantity conversion contracts.

`ExactDecimal` deliberately offers no arithmetic, because a stored value is never computed on. A
conversion does compute, and it must do so without a float ever appearing: the product of an amount
and a rate is built digit by digit here and stays a canonical literal from end to end. Rounding is a
separate, named step rather than a consequence of the multiplication, so the unrounded product
remains available to `ConvertedMoneyValue` and `ConvertedQuantityValue` and the relationship between
the two stays checkable.

Nothing in this class touches storage. It exists so that conversion arithmetic has one implementation
instead of one per caller, and so that `ExactDecimal` itself is not widened to carry operators it has
no use for.

@since  0.1.0

Kind: `class`; source: `src/Decimal/ExactDecimalArithmetic.php`.

### Constants

```json
{
    "MAXIMUM_PRECISION": {
        "visibility": "public",
        "final": false,
        "type": null,
        "value": 65
    }
}
```

### fromLiteral

```text
Reconstitute a canonical literal at the narrowest precision and scale that hold it exactly.

Provenance travels as text — in an export cell, a report column, an API response — so a converted
amount has to be rebuildable from its literals alone. A canonical literal always carries exactly
its own scale in fractional digits and no insignificant leading zero, so both are recoverable and
the value comes back byte-identical without the export having to disclose a field's digit budget.

@param   string  $value  Canonical base-10 literal as `ExactDecimal::value()` produced it.

@return  ExactDecimal  The same value, at its own minimal precision and its own scale.

@throws  InvalidArgumentException  When the literal is not canonical base-10, or needs more digits
         than a portable exact value allows.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
}
```

### multiply

```text
Multiply two exact values into their full-width product, losing nothing.

The product's scale is exactly the sum of the operands' scales, which is the only scale at which
the multiplication is lossless; narrowing it is `round()`'s job and the caller's declared choice.
The result is carried at the portable maximum precision because it belongs to no field — it is
the intermediate the conversion later rounds and publishes as evidence.

@param   ExactDecimal  $left   Left operand, typically the amount being converted.
@param   ExactDecimal  $right  Right operand, typically the rate applied to it.

@return  ExactDecimal  The exact product, carrying the summed scale of both operands.

@throws  InvalidArgumentException  When the summed scale, or the product's digit count, is wider
         than a portable exact value can hold.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "left",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "right",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
}
```

### round

```text
Narrow an exact value to a declared scale under a declared rounding rule.

Widening is allowed and is not rounding: a value already inside the requested scale is simply
re-stated at the target precision, so a caller never has to branch on whether rounding was
needed. Narrowing splits the digits, hands the four facts that decide the outcome to the declared
`ExactRoundingRule`, and increments the retained magnitude when the rule says so.

@param   ExactDecimal       $value      Value to narrow, usually the unrounded conversion product.
@param   int                $precision  Total digit budget of the field the result belongs to.
@param   int                $scale      Fractional digits the result keeps.
@param   ExactRoundingRule  $mode       Declared rule applied to the discarded digits.

@return  ExactDecimal  The value at $scale fractional digits under $mode.

@throws  InvalidArgumentException  When precision or scale is outside the portable range, or the
         rounded result no longer fits the requested precision.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "precision",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "scale",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "mode",
            "type": "Kumwe\\Conversion\\Decimal\\ExactRoundingRule",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
}
```

## Kumwe\Conversion\Decimal\ExactRoundingRule

The decision a declared rounding rule makes about the digits a target scale discards.

Money and quantity conversions narrow their exact product the same way, and `ExactDecimalArithmetic`
has no idea which of the two it is narrowing, so the two contracts share this one contract and nothing
else. Each still names its own vocabulary — `MoneyRoundingMode` and `QuantityRoundingMode` — because
which modes a business offers for a currency and for a unit of measure are separate declarations that
travel in separate payloads, and collapsing them into one enum would let a money conversion be
described in a quantity's terms.

Implementations are backed enums, so a mode is a value an export can carry and a reader can resolve,
not a strategy object a caller has to construct.

@since  0.1.0

Kind: `interface`; source: `src/Decimal/ExactRoundingRule.php`.

### increments

```text
Decide whether the kept digits are incremented once the dropped digits are known.

The caller has already split the exact product into the digits the target scale keeps and the
digits it drops, so an implementation never sees a number — only the four facts every rounding
rule is expressed in. That is what lets `ExactDecimalArithmetic` stay pure digit handling and a
new mode be added without touching it.

@param   int   $firstDropped          First discarded digit, 0 through 9.
@param   bool  $remainderBeyondFirst  Whether any discarded digit after the first is non-zero.
@param   bool  $lastKeptOdd           Whether the last retained digit is odd.
@param   bool  $negative              Whether the value being rounded is below zero.

@return  bool  True when the retained magnitude is incremented by one at its last digit.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "firstDropped",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "remainderBeyondFirst",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "lastKeptOdd",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "negative",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Provider\MoneyConversionPipeline

The single path from a conversion request to an evidenced converted amount.

Everything that converts money in a Kumwe installation comes through here, which is the point: two
extensions converting the same amount produce the same shape and the same provenance because neither
of them owns the step that produces it. The pipeline asks each contributed provider in declared order
whether it will answer, takes the first rate offered, and applies it through the core converter.

A provider's answer is not taken on trust. A rate attributed to a different provider, or one that
prices another pair or postdates the instant asked about, is refused rather than converted with, so a
package cannot launder an unattributable rate through the platform's own contract.

@since  0.1.0

Kind: `class`; source: `src/Provider/MoneyConversionPipeline.php`.

### __construct

```text
Compose the pipeline from the core conversion rule and the active provider catalog.

@param  MoneyConverter            $converter  Core rule applying a rate and its declared rounding.
@param  MoneyRateProviderCatalog  $catalog    Providers currently entitled to answer, in resolution order.

@since  0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "converter",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConverter",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "catalog",
            "type": "Kumwe\\Conversion\\Provider\\MoneyRateProviderCatalog",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### convert

```text
Convert one amount through the first contributed provider that answers.

@param   MoneyConversionRequest  $request  Amount, target currency, as-at instant and declared rounding.

@return  ConvertedMoneyValue  The presented figure, marked as converted and carrying its whole provenance.

@throws  MoneyRateUnavailable  When no contributed provider answers, or the one that did returned a rate
         it is not entitled to, prices another pair, or dates after the instant asked about.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedMoneyValue"
}
```

## Kumwe\Conversion\Provider\MoneyRateProvider

The port an extension implements to be the source of an exchange rate.

Rate sourcing is deliberately outside core. An external rate service, a manually administered table,
a bank feed and a contractual fixed rate are all implementations of this one contract, and none of
them is wired into core: a provider reaches the conversion pipeline only through the catalog its
host composes — in Kumwe App, by being contributed through the extension registrar — and it is
withdrawn with the rest of its package on disable, uninstall or trust revocation.

The business rule lives entirely behind this interface — which rate, from whom, applying when,
approved by whom. What core holds the implementation to is only that the rate it returns is complete:
it prices the pair that was asked about, it is not dated after the instant that was asked about, and
it is attributed to this provider by name.

@since  0.1.0

Kind: `interface`; source: `src/Provider/MoneyRateProvider.php`.

### identifier

```text
The identity this provider is registered under and attributes every rate to.

@return  string  The same namespaced identifier as its contributed declaration.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

### rateFor

```text
Supply the rate this provider stands behind for one conversion.

@param   MoneyConversionRequest  $request  Amount, target currency and as-at instant being asked about.

@return  MoneyExchangeRate  A rate pricing the requested pair, as at that instant or earlier, attributed
         to this provider.

@throws  MoneyRateUnavailable  When the provider accepted the request but cannot source a rate for it.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate"
}
```

### supports

```text
Whether this provider is prepared to answer one conversion.

Returning false is an ordinary outcome, not a failure: the pipeline moves on to the next declared
provider. Use it for the conditions the provider knows cheaply — an unpriced pair, an instant
outside the range it holds — and leave a genuine sourcing failure to `rateFor()`.

@param   MoneyConversionRequest  $request  Amount, target currency and as-at instant being asked about.

@return  bool  True when `rateFor()` may be called for this request.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Provider\MoneyRateProviderCatalog

The rate providers entitled to answer one conversion, in the order they are consulted.

The pipeline is a rule about how a rate is chosen and applied; which candidates exist is a
composition concern, which is why it arrives through this port instead. The catalog also applies the
bound a package accepted at install — a provider that did not declare both currencies is not offered
the conversion at all — so a package cannot widen its reach by changing its runtime behaviour after
admission.

A host supplies the implementation of this port. The Kumwe App implementation reads its extension
contribution registry and therefore returns nothing in an installation with no rate package
installed, which is the intended default.

@since  0.1.0

Kind: `interface`; source: `src/Provider/MoneyRateProviderCatalog.php`.

### providersFor

```text
List the providers currently entitled to answer one conversion.

@param   MoneyConversionRequest  $request  Conversion a caller is looking for a rate for.

@return  list<MoneyRateProvider>  Entitled providers in resolution order; empty when none is contributed
         or none declared both currencies.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\MoneyConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "array"
}
```

## Kumwe\Conversion\Provider\MoneyRateUnavailable

Refusal raised when no contributed provider can supply a rate for a conversion.

An installation with no rate provider is the ordinary state of core, so this is a stated outcome
rather than a defect: the caller presents the stored amount instead of an unevidenced converted one.

@since  0.1.0

Kind: `class`; source: `src/Provider/MoneyRateUnavailable.php`.

## Kumwe\Conversion\Provider\UnitConversionPipeline

The single path from a unit conversion request to an evidenced converted quantity.

Everything that converts a quantity in a Kumwe installation comes through here, which is the point: a
stock extension and a sales extension agree about what a case of a product is because neither of them
owns the step that decides it. The pipeline asks each contributed provider in declared order whether
it will answer, takes the first factor offered, and applies it through the core converter.

A provider's answer is not taken on trust. A factor attributed to a different provider, or one that
relates other units or postdates the instant asked about, is refused rather than converted with, so a
package cannot launder an unattributable factor through the platform's own contract.

@since  0.1.0

Kind: `class`; source: `src/Provider/UnitConversionPipeline.php`.

### __construct

```text
Compose the pipeline from the core conversion rule and the active provider catalog.

@param  QuantityConverter              $converter  Core rule applying a factor and its declared rounding.
@param  UnitConversionProviderCatalog  $catalog    Providers currently entitled to answer, in resolution
        order.

@since  0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "converter",
            "type": "Kumwe\\Conversion\\Contract\\QuantityConverter",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "catalog",
            "type": "Kumwe\\Conversion\\Provider\\UnitConversionProviderCatalog",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### convert

```text
Convert one quantity through the first contributed provider that answers.

@param   UnitConversionRequest  $request  Quantity, target unit, as-at instant and declared rounding.

@return  ConvertedQuantityValue  The expressed figure, marked as converted and carrying its whole
         provenance.

@throws  UnitConversionUnavailable  When no contributed provider answers, or the one that did returned a
         factor it is not entitled to, relates other units, or dates after the instant asked about.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedQuantityValue"
}
```

## Kumwe\Conversion\Provider\UnitConversionProvider

The port an extension implements to be the source of a unit conversion factor.

The conversion table is deliberately outside core. A metric standards table, a trade-unit table
administered by hand, a supplier feed and a contractual case size are all implementations of this one
contract, and none of them is wired into core: a provider reaches the conversion pipeline only through
the catalog its host composes — in Kumwe App, by being contributed through the extension registrar —
and it is withdrawn with the rest of its package on disable, uninstall or trust revocation.

The business rule lives entirely behind this interface — which factor, from whom, applying when,
approved by whom. What core holds the implementation to is only that the factor it returns is
complete: it relates the units that were asked about, it is not dated after the instant that was asked
about, and it is attributed to this provider by name.

@since  0.1.0

Kind: `interface`; source: `src/Provider/UnitConversionProvider.php`.

### factorFor

```text
Supply the factor this provider stands behind for one conversion.

@param   UnitConversionRequest  $request  Quantity, target unit and as-at instant being asked about.

@return  UnitConversionFactor  A factor relating the requested units, as at that instant or earlier,
         attributed to this provider.

@throws  UnitConversionUnavailable  When the provider accepted the request but cannot source a factor
         for it.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\UnitConversionFactor"
}
```

### identifier

```text
The identity this provider is registered under and attributes every factor to.

@return  string  The same namespaced identifier as its contributed declaration.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

### supports

```text
Whether this provider is prepared to answer one conversion.

Returning false is an ordinary outcome, not a failure: the pipeline moves on to the next declared
provider. Use it for the conditions the provider knows cheaply — an unrelated pair of units, an
instant outside the range it holds — and leave a genuine sourcing failure to `factorFor()`.

@param   UnitConversionRequest  $request  Quantity, target unit and as-at instant being asked about.

@return  bool  True when `factorFor()` may be called for this request.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Provider\UnitConversionProviderCatalog

The conversion providers entitled to answer one request, in the order they are consulted.

The pipeline is a rule about how a factor is chosen and applied; which candidates exist is a
composition concern, which is why it arrives through this port instead. The catalog also applies the
bound a package accepted at install — a provider that did not declare both units is not offered the
conversion at all — so a package cannot widen its reach by changing its runtime behaviour after
admission.

A host supplies the implementation of this port. The Kumwe App implementation reads its extension
contribution registry and therefore returns nothing in an installation with no conversion package
installed, which is the intended default.

@since  0.1.0

Kind: `interface`; source: `src/Provider/UnitConversionProviderCatalog.php`.

### providersFor

```text
List the providers currently entitled to answer one conversion.

@param   UnitConversionRequest  $request  Conversion a caller is looking for a factor for.

@return  list<UnitConversionProvider>  Entitled providers in resolution order; empty when none is
         contributed or none declared both units.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": true,
    "returns_reference": false,
    "parameters": [
        {
            "name": "request",
            "type": "Kumwe\\Conversion\\Contract\\UnitConversionRequest",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "array"
}
```

## Kumwe\Conversion\Provider\UnitConversionUnavailable

Refusal raised when no contributed provider can relate the units of a conversion.

An installation with no conversion table is the ordinary state of core, so this is a stated outcome
rather than a defect: the caller presents the stored quantity in its own unit instead of an
unevidenced converted one.

@since  0.1.0

Kind: `class`; source: `src/Provider/UnitConversionUnavailable.php`.

## Kumwe\Conversion\Value\ConvertedMoneyValue

An amount presented in a currency it is not stored in, carrying everything needed to justify it.

This is the non-negotiable half of the money conversion contract. A converted amount is never
interchangeable with a stored one: it says that it is converted, and it carries the amount and
currency it came from, the rate applied, the instant that rate was as at, the provider that supplied
the rate, the rounding rule, and the unrounded product the rounding was applied to. An operator
reading the figure can therefore tell, without asking anyone, whether they are looking at what was
agreed or at what it is worth today — and reproduce the second from the first.

The type is the enforcement, not a convention. There is no partial shape: the constructor recomputes
the product from the source amount and the rate and recomputes the rounding from the declared mode,
so a value whose numbers do not follow from its own provenance cannot be built, let alone serialized.
A converted amount is also structurally unlike a stored one — `MoneyValue::toArray()`'s `amount` and
`currency` pair appears nowhere at this export's top level — so a write path expecting a stored money
value refuses a converted one rather than quietly accepting it.

Conversion sits above storage and never enters it: a host's write-path guard admits no such
object into a record value, and its value codec refuses this export for a stored money field.

@since  0.1.0

Kind: `class`; source: `src/Value/ConvertedMoneyValue.php`.

### Properties

```json
{
    "converted": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyValue"
    },
    "rate": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate"
    },
    "rounding": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyRoundingMode"
    },
    "source": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\MoneyValue"
    },
    "unrounded": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    }
}
```

### __construct

```text
Bind a converted figure to the whole of the evidence that produced it.

@param   MoneyValue         $source     Stored amount the conversion read, in the currency it is held in.
@param   MoneyValue         $converted  Presented amount, in the currency the rate quotes.
@param   MoneyExchangeRate  $rate       Rate applied, its as-at instant, and the provider behind it.
@param   MoneyRoundingMode  $rounding   Declared rule applied to the digits the target scale discards.
@param   ExactDecimal       $unrounded  Exact product of the source amount and the rate, before rounding.

@throws  InvalidArgumentException  When the rate does not price the two currencies given, the unrounded
         product is not the exact product of the source amount and the rate, or the converted amount is
         not that product rounded under the declared mode.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "source",
            "type": "Kumwe\\Conversion\\Value\\MoneyValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "converted",
            "type": "Kumwe\\Conversion\\Value\\MoneyValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rate",
            "type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rounding",
            "type": "Kumwe\\Conversion\\Value\\MoneyRoundingMode",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "unrounded",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### detect

```text
Recognise a converted amount in an already-decoded value, whichever of its two forms it arrived in.

Every surface that renders a value asks this one question rather than testing for the object, the
export, or a `converted` key by itself. That is what makes the rendering rule enforceable: a
presenter, a projector or a report column cannot accidentally treat a converted amount as an
ordinary figure, because the recognition and the provenance come from the same place. The declared
member set has to match exactly, so an unrelated structured value that happens to carry a
`converted` flag is not mistaken for one.

@param   mixed  $value  Disclosed value being prepared for a surface.

@return  ?self  The converted amount, or null when the value is not one.

@throws  InvalidArgumentException  When the value carries exactly the declared member set but those
         members are missing, mistyped, or contradict each other — a figure that says it is
         converted and cannot prove it is refused rather than rendered bare.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "mixed",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "?Kumwe\\Conversion\\Value\\ConvertedMoneyValue"
}
```

### fromArray

```text
Rebuild a converted amount from the export shape, so a payload can be read back exactly.

Precision is not part of the export, because a reader outside the system has no business knowing
a field's digit budget; each literal is restored at the narrowest precision that holds it, which
leaves every exported literal byte-identical on the way back out.

@param   array<string, mixed>  $data  Export as `toArray()` produced it.

@return  self  The same converted amount, with its provenance restored.

@throws  InvalidArgumentException  When a member is missing, extra, mistyped, or contradicts the rest.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "data",
            "type": "array",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedMoneyValue"
}
```

### fromPortableString

```text
Read a converted amount back from its self-describing text form.

@param   string  $value  Text as `toPortableString()` wrote it.

@return  self  The same converted amount, with every element of its provenance restored.

@throws  InvalidArgumentException  When the text is not that exact grammar, or its numbers contradict
         each other.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedMoneyValue"
}
```

### isPortableString

```text
Whether one text value is a converted amount rather than a bare figure.

Surfaces that only need to tell the two apart — a report column type, a column renderer — use this
instead of parsing, so the answer comes from the one grammar rather than from a guess.

@param   string  $value  Candidate cell text.

@return  bool  True only when the text is the complete portable form, provenance included.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

### toArray

```text
Export the figure and the whole of its provenance, in the shape every surface carries.

The `converted` marker is present unconditionally and the presented figure sits under `value`
rather than at the top level, so this export can never be read as, or substituted for, a stored
`MoneyValue` export.

@return  array{
             converted: true,
             value: array{amount: string, currency: string},
             source: array{amount: string, currency: string},
             rate: array{
                 base_currency: string,
                 quote_currency: string,
                 rate: string,
                 as_at: string,
                 provider: string
             },
             rounding: array{mode: string, scale: int, unrounded_amount: string}
         }  The presented figure, what it came from, the rate that made it, and the rounding applied.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

### toPortableString

```text
Spell the figure and its provenance as one self-describing line of text.

A report cell and an export column carry a scalar, not a structure, so this is the form provenance
travels in once a figure leaves the system in an artifact somebody keeps. It is deliberately a
sentence rather than a code: the recipient of a downloaded export can read it without the system
that produced it, and `fromPortableString()` can read it back.

@return  string  For example `EUR 1234.56 converted from ZAR 25000.00 at 0.049382 as at
         2026-08-14T00:00:00.000000+00:00 by acme.rates.ecb rounded half_up from 1234.560000`.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

## Kumwe\Conversion\Value\ConvertedQuantityValue

A quantity expressed in a unit it is not stored in, carrying everything needed to justify it.

This is the non-negotiable half of the unit conversion contract, and it is the half that stops a stock
extension and a sales extension disagreeing about what a case of a product is. A converted quantity is
never interchangeable with a stored one: it says that it is converted, and it carries the quantity and
unit it came from, the factor applied, the instant that factor was as at, the provider that supplied
it, the rounding rule, and the unrounded product the rounding was applied to. An operator reading the
figure can therefore tell, without asking anyone, whether they are looking at what was counted or at
what it works out to in another unit — and reproduce the second from the first.

The type is the enforcement, not a convention. There is no partial shape: the constructor recomputes
the product from the source quantity and the factor and recomputes the rounding from the declared
mode, so a value whose numbers do not follow from its own provenance cannot be built, let alone
serialized. A converted quantity is also structurally unlike a stored one — `QuantityValue::toArray()`'s
`amount` and `unit` pair appears nowhere at this export's top level — so a write path expecting a
stored quantity refuses a converted one rather than quietly accepting it.

Conversion sits above storage and never enters it: a host's write-path guard admits no such
object into a record value, and its value codec refuses this export for a stored quantity field.

@since  0.1.0

Kind: `class`; source: `src/Value/ConvertedQuantityValue.php`.

### Properties

```json
{
    "converted": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\QuantityValue"
    },
    "factor": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\UnitConversionFactor"
    },
    "rounding": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\QuantityRoundingMode"
    },
    "source": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Value\\QuantityValue"
    },
    "unrounded": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    }
}
```

### __construct

```text
Bind a converted quantity to the whole of the evidence that produced it.

@param   QuantityValue         $source     Stored quantity the conversion read, in the unit it is held in.
@param   QuantityValue         $converted  Expressed quantity, in the unit the factor targets.
@param   UnitConversionFactor  $factor     Factor applied, its as-at instant, and the provider behind it.
@param   QuantityRoundingMode  $rounding   Declared rule applied to the digits the target scale discards.
@param   ExactDecimal          $unrounded  Exact product of the source quantity and the factor, before
         rounding.

@throws  InvalidArgumentException  When the factor does not relate the two units given, the unrounded
         product is not the exact product of the source quantity and the factor, or the converted
         quantity is not that product rounded under the declared mode.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "source",
            "type": "Kumwe\\Conversion\\Value\\QuantityValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "converted",
            "type": "Kumwe\\Conversion\\Value\\QuantityValue",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "factor",
            "type": "Kumwe\\Conversion\\Value\\UnitConversionFactor",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rounding",
            "type": "Kumwe\\Conversion\\Value\\QuantityRoundingMode",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "unrounded",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### fromArray

```text
Rebuild a converted quantity from the export shape, so a payload can be read back exactly.

Precision is not part of the export, because a reader outside the system has no business knowing
a field's digit budget; each literal is restored at the narrowest precision that holds it, which
leaves every exported literal byte-identical on the way back out.

@param   array<string, mixed>  $data  Export as `toArray()` produced it.

@return  self  The same converted quantity, with its provenance restored.

@throws  InvalidArgumentException  When a member is missing, extra, mistyped, or contradicts the rest.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "data",
            "type": "array",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedQuantityValue"
}
```

### fromPortableString

```text
Read a converted quantity back from its self-describing text form.

@param   string  $value  Text as `toPortableString()` wrote it.

@return  self  The same converted quantity, with every element of its provenance restored.

@throws  InvalidArgumentException  When the text is not that exact grammar, or its numbers contradict
         each other.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\ConvertedQuantityValue"
}
```

### isPortableString

```text
Whether one text value is a converted quantity rather than a bare figure.

Surfaces that only need to tell the two apart — a report column type, a column renderer — use this
instead of parsing, so the answer comes from the one grammar rather than from a guess.

@param   string  $value  Candidate cell text.

@return  bool  True only when the text is the complete portable form, provenance included.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

### toArray

```text
Export the figure and the whole of its provenance, in the shape every surface carries.

The `converted` marker is present unconditionally and the expressed figure sits under `value`
rather than at the top level, so this export can never be read as, or substituted for, a stored
`QuantityValue` export.

@return  array{
             converted: true,
             value: array{amount: string, unit: string},
             source: array{amount: string, unit: string},
             factor: array{
                 source_unit: string,
                 target_unit: string,
                 factor: string,
                 as_at: string,
                 provider: string
             },
             rounding: array{mode: string, scale: int, unrounded_amount: string}
         }  The expressed figure, what it came from, the factor that made it, and the rounding applied.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

### toPortableString

```text
Spell the figure and its provenance as one self-describing line of text.

A report cell and an export column carry a scalar, not a structure, so this is the form provenance
travels in once a figure leaves the system in an artifact somebody keeps. It is deliberately a
sentence rather than a code: the recipient of a downloaded export can read it without the system
that produced it, and `fromPortableString()` can read it back.

@return  string  For example `11.340 kg converted from 25.0000 lb at 0.45359237 as at
         2026-08-14T00:00:00.000000+00:00 by acme.units.trade rounded half_up from 11.339809250`.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "string"
}
```

## Kumwe\Conversion\Value\MoneyExchangeRate

One rate, the instant it was as at, and the provider that stands behind it.

Core owns this shape and nothing else about rates: it holds no table, reads no feed, and has no
opinion about which rate is correct. What it does insist on is that a rate is never an anonymous
number. A rate that cannot say when it applied and who supplied it cannot be used to convert, because
the converted amount would then be unauditable the moment it left the screen it was rendered on.

The rate reads as units of $quoteCurrency per one unit of $baseCurrency, and it is carried as an
`ExactDecimal` so the conversion never routes through a float. Whether the provider reached this rate
directly or through a base currency is its own business; what it publishes here is the single rate it
is prepared to be held to.

@since  0.1.0

Kind: `class`; source: `src/Value/MoneyExchangeRate.php`.

### Constants

```json
{
    "INSTANT_FORMAT": {
        "visibility": "public",
        "final": false,
        "type": null,
        "value": "Y-m-d\\TH:i:s.uP"
    }
}
```

### Properties

```json
{
    "asAt": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "DateTimeImmutable"
    },
    "baseCurrency": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    },
    "provider": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    },
    "quoteCurrency": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    },
    "rate": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    }
}
```

### __construct

```text
Bind a rate to the pair it prices, the instant it applied, and the provider that supplied it.

@param   string             $baseCurrency   Uppercase ISO 4217 code the rate converts from.
@param   string             $quoteCurrency  Uppercase ISO 4217 code the rate converts into.
@param   ExactDecimal       $rate           Units of $quoteCurrency per one unit of $baseCurrency, above zero.
@param   DateTimeImmutable  $asAt           Instant the rate was as at, spelled in UTC.
@param   string             $provider       Identifier of the rate provider standing behind this rate.

@throws  InvalidArgumentException  When a currency is not an ISO 4217 code, the two are the same, the
         rate is zero or negative or carries no fraction, the instant is not UTC, or the provider is
         not a namespaced identifier.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "baseCurrency",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "quoteCurrency",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "rate",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "asAt",
            "type": "DateTimeImmutable",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "provider",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### fromArray

```text
Rebuild a rate from the export shape, so provenance survives a round trip through a payload.

@param   array<string, mixed>  $data  Export as `toArray()` produced it.

@return  self  The same rate, with its literal and instant restored exactly.

@throws  InvalidArgumentException  When a member is missing, extra, mistyped, or not canonical.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "data",
            "type": "array",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\MoneyExchangeRate"
}
```

### instant

```text
Read a UTC instant back from the one spelling this contract writes.

@param   string  $value  Instant as `toArray()` spelled it.

@return  DateTimeImmutable  The same instant, in UTC.

@throws  InvalidArgumentException  When the text is not that exact spelling.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "DateTimeImmutable"
}
```

### toArray

```text
Export the rate in the canonical shape provenance travels in.

@return  array{
             base_currency: string,
             quote_currency: string,
             rate: string,
             as_at: string,
             provider: string
         }  The pair, the exact rate literal, the UTC instant, and the provider identity.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

## Kumwe\Conversion\Value\MoneyRoundingMode

The declared rule a conversion rounds by when the exact product is wider than the target scale.

Multiplying an exact amount by an exact rate almost always produces more fractional digits than the
currency keeps, so a conversion has to say what it did with them. Rounding is therefore an explicit,
named step of the conversion contract rather than a side effect: the mode travels with every
converted amount, beside the unrounded product it was applied to, so a reader can reproduce the
figure instead of trusting it. Which mode a business uses is the rate owner's rule; core only
insists that the answer is recorded.

The rules themselves are ordinary base-10 rounding and are shared with unit-of-measure conversion
through `ExactRoundingRule`; what stays specific to money is that this is the vocabulary a money
conversion is declared and exported in.

@since  0.1.0

Kind: `enum`; source: `src/Value/MoneyRoundingMode.php`.

### Enum representation and cases

```json
{
    "backing_type": "string",
    "cases": [
        {
            "name": "HalfUp",
            "value": "half_up"
        },
        {
            "name": "HalfDown",
            "value": "half_down"
        },
        {
            "name": "HalfEven",
            "value": "half_even"
        },
        {
            "name": "Ceiling",
            "value": "ceiling"
        },
        {
            "name": "Floor",
            "value": "floor"
        },
        {
            "name": "Truncate",
            "value": "truncate"
        }
    ]
}
```

### increments

```text
Decide whether the kept digits are incremented once the dropped digits are known.

The caller has already split the exact product into the digits the target scale keeps and the
digits it drops, so this method never sees a number — only the four facts every rounding rule is
expressed in. Keeping the decision here is what lets `ExactDecimalArithmetic` stay pure digit
handling and a new mode be added without touching it.

@param   int   $firstDropped          First discarded digit, 0 through 9.
@param   bool  $remainderBeyondFirst  Whether any discarded digit after the first is non-zero.
@param   bool  $lastKeptOdd           Whether the last retained digit is odd.
@param   bool  $negative              Whether the value being rounded is below zero.

@return  bool  True when the retained magnitude is incremented by one at its last digit.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "firstDropped",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "remainderBeyondFirst",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "lastKeptOdd",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "negative",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Value\MoneyValue

An exact amount bound to the ISO 4217 currency it is denominated in.

The pair is the whole value of a host money field: the host's value codec splits it across its
physical amount and currency columns, rebuilds it on read, and refuses a currency that differs
from one pinned in the field configuration. Carrying the amount as an `ExactDecimal` keeps
money out of float arithmetic, and pairing it with the currency here stops an amount from being
moved or compared without the denomination that gives it meaning.

@since  0.1.0

Kind: `class`; source: `src/Value/MoneyValue.php`.

### Properties

```json
{
    "amount": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    },
    "currency": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    }
}
```

### __construct

```text
Bind an amount to its currency.

@param   ExactDecimal  $amount    Amount in the currency's own units, at the field's precision and scale.
@param   string        $currency  Uppercase ISO 4217 alphabetic code, such as `ZAR`.

@throws  InvalidArgumentException  When the currency is not exactly three uppercase letters.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "amount",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "currency",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### toArray

```text
Export the pair in the canonical shape used for storage, checksums and API output.

@return  array{amount: string, currency: string}  The amount as its canonical decimal string, beside its code.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

## Kumwe\Conversion\Value\QuantityRoundingMode

The declared rule a unit conversion rounds by when the exact product is wider than the target scale.

Multiplying an exact quantity by an exact factor almost always produces more fractional digits than
the target unit keeps, so a conversion has to say what it did with them. Rounding is therefore an
explicit, named step of the conversion contract rather than a side effect: the mode travels with every
converted quantity, beside the unrounded product it was applied to, so a reader can reproduce the
figure instead of trusting it. Which mode a business uses is the conversion table owner's rule; core
only insists that the answer is recorded.

The vocabulary matches `MoneyRoundingMode` case for case, because the arithmetic is the same
arithmetic; it is a separate type so a quantity conversion is declared and exported in a quantity's
own terms and a payload can never claim a currency rule rounded a weight.

@since  0.1.0

Kind: `enum`; source: `src/Value/QuantityRoundingMode.php`.

### Enum representation and cases

```json
{
    "backing_type": "string",
    "cases": [
        {
            "name": "HalfUp",
            "value": "half_up"
        },
        {
            "name": "HalfDown",
            "value": "half_down"
        },
        {
            "name": "HalfEven",
            "value": "half_even"
        },
        {
            "name": "Ceiling",
            "value": "ceiling"
        },
        {
            "name": "Floor",
            "value": "floor"
        },
        {
            "name": "Truncate",
            "value": "truncate"
        }
    ]
}
```

### increments

```text
Decide whether the kept digits are incremented once the dropped digits are known.

The caller has already split the exact product into the digits the target scale keeps and the
digits it drops, so this method never sees a number — only the four facts every rounding rule is
expressed in. Keeping the decision here is what lets `ExactDecimalArithmetic` stay pure digit
handling and a new mode be added without touching it.

@param   int   $firstDropped          First discarded digit, 0 through 9.
@param   bool  $remainderBeyondFirst  Whether any discarded digit after the first is non-zero.
@param   bool  $lastKeptOdd           Whether the last retained digit is odd.
@param   bool  $negative              Whether the value being rounded is below zero.

@return  bool  True when the retained magnitude is incremented by one at its last digit.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "firstDropped",
            "type": "int",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "remainderBeyondFirst",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "lastKeptOdd",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "negative",
            "type": "bool",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "bool"
}
```

## Kumwe\Conversion\Value\QuantityValue

An exact amount bound to the unit it is measured in.

The pair is the whole value of a host quantity field: the host's value codec splits it across its
physical amount and unit columns, rebuilds it on read, and refuses a unit that differs from one
pinned in the field configuration. The unit is an opaque portable identifier — nothing in *this
type* converts between units, so two stored quantities are only comparable when their units are
identical.

The platform does convert, one level above storage: `QuantityConverter` applies a
`UnitConversionFactor` an extension supplied and returns a `ConvertedQuantityValue`, which is a
different kind of thing from this one and carries the factor, the as-at instant and the provider that
justify it. A converted quantity never comes back here — conversion reads stored values and writes
none.

@since  0.1.0

Kind: `class`; source: `src/Value/QuantityValue.php`.

### Properties

```json
{
    "amount": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    },
    "unit": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    }
}
```

### __construct

```text
Bind an amount to its unit of measure.

@param   ExactDecimal  $amount  Amount expressed in $unit, at the field's precision and scale.
@param   string        $unit    Unit identifier of up to 63 characters, such as `kg` or `m/s`.

@throws  InvalidArgumentException  When the unit is empty, over-long, or uses characters outside
         letters, digits, dot, underscore, hyphen and slash.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "amount",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "unit",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### toArray

```text
Export the pair in the canonical shape used for storage, checksums and API output.

@return  array{amount: string, unit: string}  The amount as its canonical decimal string, beside its unit.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

## Kumwe\Conversion\Value\UnitConversionFactor

One conversion factor, the instant it was as at, and the provider that stands behind it.

Core owns this shape and nothing else about unit conversion: it holds no table, reads no standard, and
has no opinion about how many litres are in a drum or how many bottles are in a case. What it does
insist on is that a factor is never an anonymous number. A factor that cannot say when it applied and
who published it cannot be used to convert, because the converted quantity would then be unauditable
the moment it left the screen it was rendered on — and unlike a currency, a packaging factor is a
commercial term that genuinely changes over time.

The factor reads as units of $targetUnit per one unit of $sourceUnit, and it is carried as an
`ExactDecimal` so the conversion never routes through a float. Whether the provider reached it
directly or through a base unit is its own business; what it publishes here is the single factor it is
prepared to be held to.

@since  0.1.0

Kind: `class`; source: `src/Value/UnitConversionFactor.php`.

### Constants

```json
{
    "INSTANT_FORMAT": {
        "visibility": "public",
        "final": false,
        "type": "string",
        "value": "Y-m-d\\TH:i:s.uP"
    },
    "UNIT_PATTERN": {
        "visibility": "public",
        "final": false,
        "type": "string",
        "value": "/^[A-Za-z0-9][A-Za-z0-9._\\/-]{0,62}$/D"
    }
}
```

### Properties

```json
{
    "asAt": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "DateTimeImmutable"
    },
    "factor": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal"
    },
    "provider": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    },
    "sourceUnit": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    },
    "targetUnit": {
        "visibility": "public",
        "static": false,
        "readonly": true,
        "type": "string"
    }
}
```

### __construct

```text
Bind a factor to the units it relates, the instant it applied, and the provider that supplied it.

@param   string             $sourceUnit  Portable identifier of the unit the factor converts from.
@param   string             $targetUnit  Portable identifier of the unit the factor converts into.
@param   ExactDecimal       $factor      Units of $targetUnit per one unit of $sourceUnit, above zero.
@param   DateTimeImmutable  $asAt        Instant the factor was as at, spelled in UTC.
@param   string             $provider    Identifier of the conversion provider standing behind it.

@throws  InvalidArgumentException  When a unit is not a bounded portable identifier, the two are the
         same, the factor is zero or negative or carries no fraction, the instant is not UTC, or the
         provider is not a namespaced identifier.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "sourceUnit",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "targetUnit",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "factor",
            "type": "Kumwe\\Conversion\\Decimal\\ExactDecimal",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "asAt",
            "type": "DateTimeImmutable",
            "by_reference": false,
            "variadic": false,
            "optional": false
        },
        {
            "name": "provider",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": null
}
```

### fromArray

```text
Rebuild a factor from the export shape, so provenance survives a round trip through a payload.

@param   array<string, mixed>  $data  Export as `toArray()` produced it.

@return  self  The same factor, with its literal and instant restored exactly.

@throws  InvalidArgumentException  When a member is missing, extra, mistyped, or not canonical.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "data",
            "type": "array",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "Kumwe\\Conversion\\Value\\UnitConversionFactor"
}
```

### instant

```text
Read a UTC instant back from the one spelling this contract writes.

@param   string  $value  Instant as `toArray()` spelled it.

@return  DateTimeImmutable  The same instant, in UTC.

@throws  InvalidArgumentException  When the text is not that exact spelling.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": true,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [
        {
            "name": "value",
            "type": "string",
            "by_reference": false,
            "variadic": false,
            "optional": false
        }
    ],
    "return_type": "DateTimeImmutable"
}
```

### toArray

```text
Export the factor in the canonical shape provenance travels in.

@return  array{
             source_unit: string,
             target_unit: string,
             factor: string,
             as_at: string,
             provider: string
         }  The two units, the exact factor literal, the UTC instant, and the provider identity.

@since   0.1.0
```

```json
{
    "visibility": "public",
    "static": false,
    "final": false,
    "abstract": false,
    "returns_reference": false,
    "parameters": [],
    "return_type": "array"
}
```

