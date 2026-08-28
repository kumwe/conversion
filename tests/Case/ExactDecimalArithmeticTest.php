<?php

/**
 * Replays the App's exact-arithmetic corpus: lossless digit multiplication,
 * declared rounding at every boundary the App pins, literal reconstitution,
 * and the width refusals of the portable range. The rounding vocabulary is a
 * suite fixture implementing ExactRoundingRule with the decision table the
 * App's money and quantity modes share, so the kernel is proven through its
 * own port.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Decimal\ExactRoundingRule;
use Kumwe\Conversion\Tests\TestCase;

final class ExactDecimalArithmeticTest extends TestCase
{
    public function testMultiplicationIsExactAndKeepsTheSummedScale(): void
    {
        $amount = ExactDecimal::fromString('25000.00', 12, 2);
        $rate = ExactDecimal::fromString('0.04938240', 12, 8);

        $product = ExactDecimalArithmetic::multiply($amount, $rate);
        $this->assertSame('1234.5600000000', $product->value(), 'The App money fixture must multiply exactly.');
        $this->assertSame(10, $product->scale, 'The product scale is the sum of the operand scales.');
        $this->assertSame(
            ExactDecimalArithmetic::MAXIMUM_PRECISION,
            $product->precision,
            'A product belongs to no field and is carried at the portable maximum precision.'
        );
        $this->assertSame(
            $product->value(),
            ExactDecimalArithmetic::multiply($amount, $rate)->value(),
            'The same operands must produce identical bytes on every run.'
        );

        $this->assertSame(
            '11.339809250000',
            ExactDecimalArithmetic::multiply(
                ExactDecimal::fromString('25.0000', 12, 4),
                ExactDecimal::fromString('0.45359237', 12, 8)
            )->value(),
            'The App quantity fixture must multiply exactly.'
        );
        $this->assertSame(
            '-1234.5600000000',
            ExactDecimalArithmetic::multiply(
                ExactDecimal::fromString('-25000.00', 12, 2),
                $rate
            )->value(),
            'A negative operand negates the product.'
        );
        $this->assertSame(
            '1234.5600000000',
            ExactDecimalArithmetic::multiply(
                ExactDecimal::fromString('-25000.00', 12, 2),
                ExactDecimal::fromString('-0.04938240', 12, 8)
            )->value(),
            'Two negative operands produce a positive product.'
        );
        $this->assertSame(
            '0.0000000000',
            ExactDecimalArithmetic::multiply(ExactDecimal::fromString('0.00', 12, 2), $rate)->value(),
            'A zero operand yields canonical zero at the summed scale, never negative zero.'
        );
    }

    public function testOperandsBeyondTheIntegerRangeMultiplyExactly(): void
    {
        $this->assertSame(
            '111111110111111111010',
            ExactDecimalArithmetic::multiply(
                ExactDecimal::fromString('12345678901234567890', 65, 0),
                ExactDecimal::fromString('9', 65, 0)
            )->value(),
            'A twenty-digit operand must multiply without passing through a PHP number.'
        );
        $this->assertSame(
            '9999999999999999999800000000000000000001',
            ExactDecimalArithmetic::multiply(
                ExactDecimal::fromString(str_repeat('9', 20), 65, 0),
                ExactDecimal::fromString(str_repeat('9', 20), 65, 0)
            )->value(),
            'Squaring twenty nines must carry across the whole forty-digit product.'
        );
    }

    public function testAProductWiderThanThePortableRangeIsRefused(): void
    {
        $wide = ExactDecimal::fromString('0.' . str_repeat('1', 33), 65, 33);
        $summedScale = $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::multiply($wide, $wide),
            'wider than the portable decimal range'
        );
        $this->assertTrue(
            $summedScale instanceof InvalidArgumentException,
            'A summed scale beyond 65 must be refused as an invalid argument.'
        );

        $huge = ExactDecimal::fromString(str_repeat('9', 33), 65, 0);
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::multiply($huge, $huge),
            'exceeds the field precision'
        );
    }

    public function testEveryRoundingRuleDecidesTheDiscardedDigitsAtTheBoundary(): void
    {
        $expected = [
            'half_up' => ['1.24', '-1.24'],
            'half_down' => ['1.23', '-1.23'],
            'half_even' => ['1.24', '-1.24'],
            'ceiling' => ['1.24', '-1.23'],
            'floor' => ['1.23', '-1.24'],
            'truncate' => ['1.23', '-1.23'],
        ];

        foreach (SuiteRoundingRule::cases() as $mode) {
            $positive = ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2350', 8, 4),
                8,
                2,
                $mode
            );
            $negative = ExactDecimalArithmetic::round(
                ExactDecimal::fromString('-1.2350', 8, 4),
                8,
                2,
                $mode
            );
            $this->assertSame(
                $expected[$mode->value][0],
                $positive->value(),
                'Positive tie under ' . $mode->value . ' must match the App answer.'
            );
            $this->assertSame(
                $expected[$mode->value][1],
                $negative->value(),
                'Negative tie under ' . $mode->value . ' must match the App answer.'
            );
        }

        $this->assertSame(
            '1.22',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2250', 8, 4),
                8,
                2,
                SuiteRoundingRule::HalfEven
            )->value(),
            'An even-neighbour tie must round down under half_even.'
        );
        $this->assertSame(
            '10.0',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('9.99', 8, 2),
                8,
                1,
                SuiteRoundingRule::HalfUp
            )->value(),
            'A rounding carry must propagate into the integer digits.'
        );
        $this->assertSame(
            '1.23',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2340', 8, 4),
                8,
                2,
                SuiteRoundingRule::HalfUp
            )->value(),
            'A first dropped digit below the tie must never increment under half_up.'
        );
        $this->assertSame(
            '1.24',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.23501', 8, 5),
                8,
                2,
                SuiteRoundingRule::HalfDown
            )->value(),
            'A remainder beyond the tie digit must lift half_down over the tie.'
        );
        $this->assertSame(
            '1.24',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.23001', 8, 5),
                8,
                2,
                SuiteRoundingRule::Ceiling
            )->value(),
            'Any positive remainder must move ceiling upward.'
        );
        $this->assertSame(
            '-1.24',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('-1.23001', 8, 5),
                8,
                2,
                SuiteRoundingRule::Floor
            )->value(),
            'Any negative remainder must move floor downward.'
        );
        $this->assertSame(
            '0.00',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('-0.0040', 8, 4),
                8,
                2,
                SuiteRoundingRule::Truncate
            )->value(),
            'A truncation that discards the whole magnitude must yield zero, never negative zero.'
        );
    }

    public function testWideningIsARestatementAndNotARounding(): void
    {
        $this->assertSame(
            '1.2300',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.23', 8, 2),
                10,
                4,
                SuiteRoundingRule::Truncate
            )->value(),
            'A value inside the requested scale is re-stated, not rounded.'
        );
        $this->assertSame(
            '1.2345',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2345', 8, 4),
                10,
                4,
                SuiteRoundingRule::HalfUp
            )->value(),
            'An equal scale passes through unchanged whatever the mode.'
        );
    }

    public function testARoundedResultThatNoLongerFitsThePrecisionIsRefused(): void
    {
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::round(
                ExactDecimal::fromString('9.99', 8, 2),
                2,
                1,
                SuiteRoundingRule::HalfUp
            ),
            'exceeds the field precision'
        );
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.23', 8, 2),
                0,
                0,
                SuiteRoundingRule::Truncate
            ),
            'outside the portable database range'
        );
    }

    public function testALiteralRestoresAtItsOwnMinimalPrecisionAndScale(): void
    {
        $rate = ExactDecimalArithmetic::fromLiteral('0.04938240');
        $this->assertSame('0.04938240', $rate->value(), 'A literal must restore byte-identical.');
        $this->assertSame(8, $rate->scale, 'A literal carries exactly its own fractional digits as scale.');
        $this->assertSame(8, $rate->precision, 'A leading zero is insignificant to the minimal precision.');

        $product = ExactDecimalArithmetic::fromLiteral('1234.5600000000');
        $this->assertSame('1234.5600000000', $product->value(), 'Trailing zeros are significant and kept.');
        $this->assertSame(10, $product->scale, 'The restored scale is read from the literal.');
        $this->assertSame(14, $product->precision, 'The restored precision is integer digits plus scale.');

        $zero = ExactDecimalArithmetic::fromLiteral('0');
        $this->assertSame('0', $zero->value(), 'Zero restores as zero.');
        $this->assertSame(1, $zero->precision, 'Zero occupies the minimal single-digit precision.');
        $this->assertSame(0, $zero->scale, 'Zero without a fraction has scale zero.');

        $this->assertSame('-12.50', ExactDecimalArithmetic::fromLiteral('-12.50')->value(), 'Sign round trips.');
    }

    public function testANonCanonicalOrOverwideLiteralIsRefused(): void
    {
        foreach (['+1', '01', '1.', '.5', '1e3', '', '1,5', '1.2.3'] as $literal) {
            $this->refuses(
                static fn (): ExactDecimal => ExactDecimalArithmetic::fromLiteral($literal),
                'canonical base-10'
            );
        }
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::fromLiteral(str_repeat('9', 66)),
            'outside the portable database range'
        );
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimalArithmetic::fromLiteral('0.' . str_repeat('1', 66)),
            'outside the portable database range'
        );
    }

    /**
     * Require one operation to be refused, and its reason to name the rule it broke.
     *
     * @param callable(): mixed $operation Operation expected to fail.
     * @param string            $reason    Fragment the refusal message must contain.
     */
    private function refuses(callable $operation, string $reason): \Throwable
    {
        $error = $this->assertThrows(
            $operation,
            InvalidArgumentException::class,
            sprintf('An operation violating "%s" succeeded.', $reason)
        );
        $this->assertStringContains($reason, $error->getMessage(), 'The refusal must name the rule broken.');

        return $error;
    }
}

/**
 * The suite's own rounding vocabulary: the decision table the App's money and
 * quantity rounding modes share, restated here so the kernel's round() is
 * proven through the ExactRoundingRule port without reaching above the
 * Decimal layer.
 *
 * @since 0.1.0
 */
enum SuiteRoundingRule: string implements ExactRoundingRule
{
    case HalfUp = 'half_up';
    case HalfDown = 'half_down';
    case HalfEven = 'half_even';
    case Ceiling = 'ceiling';
    case Floor = 'floor';
    case Truncate = 'truncate';

    /**
     * Decide whether the kept digits are incremented once the dropped digits are known.
     *
     * @param int  $firstDropped         First discarded digit, 0 through 9.
     * @param bool $remainderBeyondFirst Whether any discarded digit after the first is non-zero.
     * @param bool $lastKeptOdd          Whether the last retained digit is odd.
     * @param bool $negative             Whether the value being rounded is below zero.
     */
    public function increments(
        int $firstDropped,
        bool $remainderBeyondFirst,
        bool $lastKeptOdd,
        bool $negative,
    ): bool {
        $remainder = $firstDropped > 0 || $remainderBeyondFirst;

        return match ($this) {
            self::HalfUp => $firstDropped >= 5,
            self::HalfDown => $firstDropped > 5 || ($firstDropped === 5 && $remainderBeyondFirst),
            self::HalfEven => $firstDropped > 5
                || ($firstDropped === 5 && ($remainderBeyondFirst || $lastKeptOdd)),
            self::Ceiling => $remainder && !$negative,
            self::Floor => $remainder && $negative,
            self::Truncate => false,
        };
    }
}
