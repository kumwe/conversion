<?php

/**
 * Replays the App's unit conversion contract through the converter: exact
 * multiplication with the unrounded product kept, and refusal of a factor
 * that answers a different question than the one asked.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kumwe\Conversion\Contract\QuantityConverter;
use Kumwe\Conversion\Contract\UnitConversionRequest;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\ConvertedQuantityValue;
use Kumwe\Conversion\Value\QuantityRoundingMode;
use Kumwe\Conversion\Value\QuantityValue;
use Kumwe\Conversion\Value\UnitConversionFactor;

final class QuantityConverterTest extends TestCase
{
    public function testConversionMultipliesExactlyAndKeepsTheUnroundedProduct(): void
    {
        $converted = (new QuantityConverter())->convert(self::request(), self::factor('0.45359237'));

        $this->assertSame('11.339809250000', $converted->unrounded->value(), 'The unrounded product is kept.');
        $this->assertSame('11.340', $converted->converted->amount->value(), 'The figure is the rounded product.');
        $this->assertSame('kg', $converted->converted->unit, 'The figure is in the target unit.');
        $this->assertSame('lb', $converted->source->unit, 'The source stays in its own unit.');
        $this->assertSame(
            '11.339809250000',
            ExactDecimalArithmetic::multiply(
                $converted->source->amount,
                $converted->factor->factor
            )->value(),
            'The carried product must be reproducible from the carried evidence.'
        );
    }

    public function testTheConvertedResultCarriesItsWholeProvenance(): void
    {
        $exported = (new QuantityConverter())->convert(self::request(), self::factor('0.45359237'))->toArray();

        $this->assertTrue($exported['converted'], 'The result is always marked as converted.');
        $this->assertSame('acme.units.trade', $exported['factor']['provider'], 'The provider identity is carried.');
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $exported['factor']['as_at'],
            'The as-at instant is carried.'
        );
        $this->assertSame('half_up', $exported['rounding']['mode'], 'The declared rounding is carried.');
        $this->assertSame(
            '11.339809250000',
            $exported['rounding']['unrounded_amount'],
            'The unrounded product is carried.'
        );
    }

    public function testAFactorFromAfterTheInstantAskedAboutIsRefused(): void
    {
        $this->refuses(
            static fn (): ConvertedQuantityValue => (new QuantityConverter())->convert(
                self::request(),
                self::factor('0.45359237', '2026-08-15T00:00:00')
            ),
            'as at the instant asked about'
        );
    }

    public function testAFactorRelatingAnotherPairIsRefused(): void
    {
        $this->refuses(
            static fn (): ConvertedQuantityValue => (new QuantityConverter())->convert(
                self::request(),
                new UnitConversionFactor(
                    'lb',
                    'g',
                    ExactDecimalArithmetic::fromLiteral('453.59237000'),
                    new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
                    'acme.units.trade'
                )
            ),
            'must relate the requested units'
        );
    }

    /**
     * The one conversion every case in this class asks for.
     */
    private static function request(): UnitConversionRequest
    {
        return new UnitConversionRequest(
            new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb'),
            'kg',
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
            12,
            3,
            QuantityRoundingMode::HalfUp
        );
    }

    /**
     * Build one factor for the fixture pair, attributed to the test conversion package.
     *
     * @param string $factor Canonical factor literal, kilograms per pound.
     * @param string $asAt   Naive UTC instant the factor is as at.
     */
    private static function factor(string $factor, string $asAt = '2026-08-14T00:00:00'): UnitConversionFactor
    {
        return new UnitConversionFactor(
            'lb',
            'kg',
            ExactDecimalArithmetic::fromLiteral($factor),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            'acme.units.trade'
        );
    }

    /**
     * Require one conversion to be refused, and its reason to name the rule it broke.
     *
     * @param callable(): mixed $conversion Conversion expected to fail.
     * @param string            $reason     Fragment the refusal message must contain.
     */
    private function refuses(callable $conversion, string $reason): void
    {
        $error = $this->assertThrows(
            $conversion,
            InvalidArgumentException::class,
            sprintf('A conversion violating "%s" succeeded.', $reason)
        );
        $this->assertStringContains($reason, $error->getMessage(), 'The refusal must name the rule broken.');
    }
}
