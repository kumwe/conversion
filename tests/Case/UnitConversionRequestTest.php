<?php

/**
 * Replays the App's request contract for units: the degenerate conversions
 * a request refuses at construction, and the one-place rule deciding
 * whether an offered factor answers the question asked.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kumwe\Conversion\Contract\UnitConversionRequest;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\QuantityRoundingMode;
use Kumwe\Conversion\Value\QuantityValue;
use Kumwe\Conversion\Value\UnitConversionFactor;

final class UnitConversionRequestTest extends TestCase
{
    public function testAConversionRequestRefusesAUnitItWouldNotChange(): void
    {
        $this->refuses(
            static fn (): UnitConversionRequest => new UnitConversionRequest(
                new QuantityValue(ExactDecimal::fromString('1.000', 12, 3), 'kg'),
                'kg',
                new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
                12,
                3,
                QuantityRoundingMode::HalfUp
            ),
            'other than the quantity\'s own'
        );
        $this->refuses(
            static fn (): UnitConversionRequest => new UnitConversionRequest(
                new QuantityValue(ExactDecimal::fromString('1.000', 12, 3), 'kg'),
                'metric tonne',
                new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
                12,
                3,
                QuantityRoundingMode::HalfUp
            ),
            'bounded portable identifier'
        );
    }

    public function testARequestAsAtInstantMustBeExpressedInUtc(): void
    {
        foreach (['Africa/Windhoek', 'America/New_York', '+02:00'] as $zone) {
            $this->refuses(
                static fn (): UnitConversionRequest => new UnitConversionRequest(
                    new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb'),
                    'kg',
                    new DateTimeImmutable('2026-08-14T02:00:00', new DateTimeZone($zone)),
                    12,
                    3,
                    QuantityRoundingMode::HalfUp
                ),
                'must be expressed in UTC'
            );
        }
    }

    public function testARequestPrecisionAndScaleMustLieInThePortableRange(): void
    {
        $outside = [
            [0, 0],
            [-1, 0],
            [ExactDecimalArithmetic::MAXIMUM_PRECISION + 1, 3],
            [12, -1],
            [12, 13],
        ];

        foreach ($outside as $shape) {
            $this->refuses(
                static fn (): UnitConversionRequest => self::request($shape[0], $shape[1]),
                'outside the portable range'
            );
        }

        $widest = self::request(ExactDecimalArithmetic::MAXIMUM_PRECISION, 0);
        $this->assertSame(
            ExactDecimalArithmetic::MAXIMUM_PRECISION,
            $widest->precision,
            'The widest portable precision must be admitted.'
        );
        $this->assertSame(0, $widest->scale, 'A zero scale must be admitted.');
    }

    public function testAFactorAnswersOnlyWhenItRelatesThePairAsAtTheInstantOrEarlier(): void
    {
        $request = self::request();

        $this->assertTrue(
            $request->answeredBy(self::factor('2026-08-14T00:00:00')),
            'A factor as at the instant asked about answers the request.'
        );
        $this->assertTrue(
            $request->answeredBy(self::factor('2026-08-13T00:00:00')),
            'An earlier factor answers the request.'
        );
        $this->assertTrue(
            !$request->answeredBy(self::factor('2026-08-15T00:00:00')),
            'A factor dated after the instant asked about answers a different question.'
        );
        $this->assertTrue(
            !$request->answeredBy(new UnitConversionFactor(
                'lb',
                'g',
                ExactDecimal::fromString('453.59237000', 14, 8),
                new DateTimeImmutable('2026-08-13T00:00:00', new DateTimeZone('UTC')),
                'acme.units.trade'
            )),
            'A factor relating another pair answers a different question.'
        );
    }

    /**
     * Build the fixture request at the requested answer shape.
     *
     * @param int $precision Total digit budget the converted quantity is expressed at.
     * @param int $scale     Fractional digits the target unit keeps.
     */
    private static function request(int $precision = 12, int $scale = 3): UnitConversionRequest
    {
        return new UnitConversionRequest(
            new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb'),
            'kg',
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
            $precision,
            $scale,
            QuantityRoundingMode::HalfUp
        );
    }

    /**
     * Build one factor for the fixture pair, as at the given naive UTC instant.
     *
     * @param string $asAt Naive UTC instant the factor is as at.
     */
    private static function factor(string $asAt): UnitConversionFactor
    {
        return new UnitConversionFactor(
            'lb',
            'kg',
            ExactDecimalArithmetic::fromLiteral('0.45359237'),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            'acme.units.trade'
        );
    }

    /**
     * Require one construction to be refused, and its reason to name the rule it broke.
     *
     * @param callable(): mixed $construction Construction expected to fail.
     * @param string            $reason       Fragment the refusal message must contain.
     */
    private function refuses(callable $construction, string $reason): void
    {
        $error = $this->assertThrows(
            $construction,
            InvalidArgumentException::class,
            sprintf('A value violating "%s" was constructed.', $reason)
        );
        $this->assertStringContains($reason, $error->getMessage(), 'The refusal must name the rule broken.');
    }
}
