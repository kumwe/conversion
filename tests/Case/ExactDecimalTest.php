<?php

/**
 * Replays the App's exact-decimal corpus: canonical form, the 65-digit
 * portable boundary, digit-walking comparison, and every documented
 * construction refusal of the kernel's value type.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Tests\TestCase;

final class ExactDecimalTest extends TestCase
{
    public function testDecimalWithEqualPrecisionAndScaleAcceptsZeroAndMaximumFraction(): void
    {
        $this->assertSame(
            '0.' . str_repeat('0', 65),
            ExactDecimal::fromString('0', 65, 65)->value(),
            'Zero at precision 65, scale 65 must pad to sixty-five fractional zeros.'
        );
        $this->assertSame(
            '0.' . str_repeat('9', 65),
            ExactDecimal::fromString('0.' . str_repeat('9', 65), 65, 65)->value(),
            'The maximum fraction at precision 65, scale 65 must survive byte for byte.'
        );
    }

    public function testPortableSixtyFiveDigitBoundaryRejectsOverflow(): void
    {
        $this->assertSame(
            str_repeat('9', 65),
            ExactDecimal::fromString(str_repeat('9', 65), 65, 0)->value(),
            'Sixty-five integer digits are the widest portable value and must be admitted.'
        );
        $this->assertThrows(
            static fn (): ExactDecimal => ExactDecimal::fromString(str_repeat('9', 66), 65, 0),
            InvalidArgumentException::class,
            'A sixty-six digit value must be refused at the portable boundary.'
        );
    }

    public function testGeneratedCanonicalValuesRoundTripWithoutFloatLoss(): void
    {
        mt_srand(20260808);
        for ($iteration = 0; $iteration < 500; ++$iteration) {
            $integer = (string) mt_rand(0, 999_999);
            $fraction = str_pad((string) mt_rand(0, 9_999), 4, '0', STR_PAD_LEFT);
            $value = ($iteration % 2 === 0 ? '-' : '') . $integer . '.' . $fraction;
            $this->assertSame(
                $value,
                ExactDecimal::fromString($value, 10, 4)->value(),
                'A generated canonical value must round trip byte for byte.'
            );
        }
    }

    public function testMaximumPrecisionAndScaleRemainExact(): void
    {
        $fraction = str_repeat('9', 65);
        $value = ExactDecimal::fromString('0.' . $fraction, 65, 65);

        $this->assertSame('0.' . $fraction, $value->value(), 'Sixty-five fractional nines must be kept exactly.');
        $this->assertSame(
            1,
            $value->compare(ExactDecimal::fromString('0.' . str_repeat('8', 65), 65, 65)),
            'Comparison at scale 65 must order digit by digit, past any float.'
        );
        $this->assertSame(
            '0.' . str_repeat('0', 65),
            ExactDecimal::fromString('-0', 65, 65)->value(),
            'Negative zero must be spelled as zero at any scale.'
        );

        $overwide = $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromString('0.' . str_repeat('1', 66), 65, 65),
            'fractional digits'
        );
        $this->assertTrue(
            $overwide instanceof InvalidArgumentException,
            'A value exceeding scale 65 must be refused as an invalid argument.'
        );
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromString('1', 65, 65),
            'precision'
        );
    }

    public function testANonCanonicalLiteralIsRefused(): void
    {
        $malformed = ['+1', '01', '1.', '.5', '1e3', '1,5', ' 1', '1 ', '', '--1', '1.2.3', "1\n"];
        foreach ($malformed as $literal) {
            $this->refuses(
                static fn (): ExactDecimal => ExactDecimal::fromString($literal, 10, 2),
                'canonical base-10 string'
            );
        }
    }

    public function testPrecisionOrScaleOutsideThePortableRangeIsRefused(): void
    {
        $outside = [[0, 0], [-1, 0], [66, 0], [5, -1], [5, 6]];
        foreach ($outside as $shape) {
            $this->refuses(
                static fn (): ExactDecimal => ExactDecimal::fromString('1', $shape[0], $shape[1]),
                'outside the portable database range'
            );
            $this->refuses(
                static fn (): ExactDecimal => ExactDecimal::fromInt(1, $shape[0], $shape[1]),
                'outside the portable database range'
            );
        }
    }

    public function testAFractionWiderThanTheFieldScaleIsRefused(): void
    {
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromString('1.234', 10, 2),
            'more fractional digits than the field scale'
        );
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromString('0.1', 10, 0),
            'more fractional digits than the field scale'
        );
    }

    public function testIntegerDigitsExceedingThePrecisionAreRefused(): void
    {
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromString('1234', 5, 2),
            'exceeds the field precision'
        );
        $this->refuses(
            static fn (): ExactDecimal => ExactDecimal::fromInt(1234, 5, 2),
            'exceeds the field precision'
        );
    }

    public function testAShorterFractionIsPaddedAndNegativeZeroIsSpelledAsZero(): void
    {
        $this->assertSame('1.5000', ExactDecimal::fromString('1.5', 10, 4)->value(), 'A short fraction is padded.');
        $this->assertSame('-0.0100', ExactDecimal::fromString('-0.01', 10, 4)->value(), 'Sign survives padding.');
        $this->assertSame('0.00', ExactDecimal::fromString('-0.00', 10, 2)->value(), 'Negative zero is zero.');
        $this->assertSame('0', ExactDecimal::fromString('-0', 5, 0)->value(), 'Negative integer zero is zero.');
        $this->assertSame('-7.00', ExactDecimal::fromInt(-7, 5, 2)->value(), 'A whole number lifts with zeros.');
        $this->assertSame('0.00', ExactDecimal::fromInt(0, 5, 2)->value(), 'Integer zero pads to the scale.');
        $this->assertSame(
            '25000.00',
            (string) ExactDecimal::fromString('25000.00', 12, 2),
            'The string cast renders the same canonical literal as value().'
        );
    }

    public function testComparisonWalksDigitsAndRefusesMismatchedScales(): void
    {
        $left = ExactDecimal::fromString('123456789012345678901234567890.1', 65, 1);
        $right = ExactDecimal::fromString('123456789012345678901234567890.2', 65, 1);

        $this->assertSame(-1, $left->compare($right), 'A last-digit difference beyond float range must order.');
        $this->assertSame(1, $right->compare($left), 'Comparison must be antisymmetric.');
        $this->assertSame(0, $left->compare($left), 'A value compares equal with itself.');
        $this->assertSame(
            -1,
            ExactDecimal::fromString('-1.00', 10, 2)->compare(ExactDecimal::fromString('0.50', 10, 2)),
            'A negative value sorts before any positive value.'
        );
        $this->assertSame(
            1,
            ExactDecimal::fromString('-1.00', 10, 2)->compare(ExactDecimal::fromString('-2.00', 10, 2)),
            'Negative comparison inverts the magnitude ordering.'
        );
        $this->assertSame(
            1,
            ExactDecimal::fromString('10.00', 10, 2)->compare(ExactDecimal::fromString('9.99', 10, 2)),
            'A longer digit string outranks a shorter one of the same sign.'
        );
        $this->refuses(
            static function (): int {
                return ExactDecimal::fromString('1.00', 10, 2)
                    ->compare(ExactDecimal::fromString('1.000', 10, 3));
            },
            'cannot be compared directly'
        );
    }

    /**
     * Require one construction to be refused, and its reason to name the rule it broke.
     *
     * @param callable(): mixed $construction Construction expected to fail.
     * @param string            $reason       Fragment the refusal message must contain.
     */
    private function refuses(callable $construction, string $reason): \Throwable
    {
        $error = $this->assertThrows(
            $construction,
            InvalidArgumentException::class,
            sprintf('A value violating "%s" was constructed.', $reason)
        );
        $this->assertStringContains($reason, $error->getMessage(), 'The refusal must name the rule broken.');

        return $error;
    }
}
