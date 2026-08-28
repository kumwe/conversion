<?php

/**
 * Replays the App's money conversion contract through the converter: exact
 * multiplication with the unrounded product kept, and refusal of a rate
 * that answers a different question than the one asked.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kumwe\Conversion\Contract\MoneyConversionRequest;
use Kumwe\Conversion\Contract\MoneyConverter;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\ConvertedMoneyValue;
use Kumwe\Conversion\Value\MoneyExchangeRate;
use Kumwe\Conversion\Value\MoneyRoundingMode;
use Kumwe\Conversion\Value\MoneyValue;

final class MoneyConverterTest extends TestCase
{
    public function testConversionMultipliesExactlyAndKeepsTheUnroundedProduct(): void
    {
        $converted = (new MoneyConverter())->convert(self::request(), self::rate('0.04938240'));

        $this->assertSame('1234.5600000000', $converted->unrounded->value(), 'The unrounded product is kept.');
        $this->assertSame('1234.56', $converted->converted->amount->value(), 'The figure is the rounded product.');
        $this->assertSame('EUR', $converted->converted->currency, 'The figure is in the target currency.');
        $this->assertSame('ZAR', $converted->source->currency, 'The source stays in its own currency.');
        $this->assertSame(
            '1234.5600000000',
            ExactDecimalArithmetic::multiply(
                $converted->source->amount,
                $converted->rate->rate
            )->value(),
            'The carried product must be reproducible from the carried evidence.'
        );
    }

    public function testTheConvertedResultCarriesItsWholeProvenance(): void
    {
        $exported = (new MoneyConverter())->convert(self::request(), self::rate('0.04938240'))->toArray();

        $this->assertTrue($exported['converted'], 'The result is always marked as converted.');
        $this->assertSame('acme.rates.ecb', $exported['rate']['provider'], 'The provider identity is carried.');
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $exported['rate']['as_at'],
            'The as-at instant is carried.'
        );
        $this->assertSame('half_up', $exported['rounding']['mode'], 'The declared rounding is carried.');
        $this->assertSame(
            '1234.5600000000',
            $exported['rounding']['unrounded_amount'],
            'The unrounded product is carried.'
        );
    }

    public function testARateFromAfterTheInstantAskedAboutIsRefused(): void
    {
        $this->refuses(
            static fn (): ConvertedMoneyValue => (new MoneyConverter())->convert(
                self::request(),
                self::rate('0.04938240', '2026-08-15T00:00:00')
            ),
            'as at the instant asked about'
        );
    }

    public function testARatePricingAnotherPairIsRefused(): void
    {
        $this->refuses(
            static fn (): ConvertedMoneyValue => (new MoneyConverter())->convert(
                self::request(),
                new MoneyExchangeRate(
                    'ZAR',
                    'USD',
                    ExactDecimalArithmetic::fromLiteral('0.04938240'),
                    new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
                    'acme.rates.ecb'
                )
            ),
            'must price the requested pair'
        );
    }

    /**
     * The one conversion every case in this class asks for.
     */
    private static function request(): MoneyConversionRequest
    {
        return new MoneyConversionRequest(
            new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR'),
            'EUR',
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
            12,
            2,
            MoneyRoundingMode::HalfUp
        );
    }

    /**
     * Build one rate for the fixture pair, attributed to the test rate package.
     *
     * @param string $rate Canonical rate literal, EUR per ZAR.
     * @param string $asAt Naive UTC instant the rate is as at.
     */
    private static function rate(string $rate, string $asAt = '2026-08-14T00:00:00'): MoneyExchangeRate
    {
        return new MoneyExchangeRate(
            'ZAR',
            'EUR',
            ExactDecimalArithmetic::fromLiteral($rate),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            'acme.rates.ecb'
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
