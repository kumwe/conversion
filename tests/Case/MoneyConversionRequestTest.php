<?php

/**
 * Replays the App's request contract: the degenerate conversions a request
 * refuses at construction, and the one-place rule deciding whether an
 * offered rate answers the question asked.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kumwe\Conversion\Contract\MoneyConversionRequest;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\MoneyExchangeRate;
use Kumwe\Conversion\Value\MoneyRoundingMode;
use Kumwe\Conversion\Value\MoneyValue;

final class MoneyConversionRequestTest extends TestCase
{
    public function testAConversionRequestRefusesADenominationItWouldNotChange(): void
    {
        $this->refuses(
            static fn (): MoneyConversionRequest => self::request(target: 'ZAR'),
            'other than the amount\'s own'
        );
        $this->refuses(
            static fn (): MoneyConversionRequest => self::request(target: 'eur'),
            'uppercase ISO 4217 code'
        );
    }

    public function testARequestAsAtInstantMustBeExpressedInUtc(): void
    {
        foreach (['Africa/Windhoek', 'America/New_York', '+02:00'] as $zone) {
            $this->refuses(
                static fn (): MoneyConversionRequest => new MoneyConversionRequest(
                    new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR'),
                    'EUR',
                    new DateTimeImmutable('2026-08-14T02:00:00', new DateTimeZone($zone)),
                    12,
                    2,
                    MoneyRoundingMode::HalfUp
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
            [ExactDecimalArithmetic::MAXIMUM_PRECISION + 1, 2],
            [12, -1],
            [12, 13],
        ];

        foreach ($outside as $shape) {
            $this->refuses(
                static fn (): MoneyConversionRequest => self::request($shape[0], $shape[1]),
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

    public function testARateAnswersOnlyWhenItPricesThePairAsAtTheInstantOrEarlier(): void
    {
        $request = self::request();

        $this->assertTrue(
            $request->answeredBy(self::rate('2026-08-14T00:00:00')),
            'A rate as at the instant asked about answers the request.'
        );
        $this->assertTrue(
            $request->answeredBy(self::rate('2026-08-13T00:00:00')),
            'An earlier rate answers the request.'
        );
        $this->assertTrue(
            !$request->answeredBy(self::rate('2026-08-15T00:00:00')),
            'A rate dated after the instant asked about answers a different question.'
        );
        $this->assertTrue(
            !$request->answeredBy(new MoneyExchangeRate(
                'ZAR',
                'USD',
                ExactDecimal::fromString('0.04938240', 12, 8),
                new DateTimeImmutable('2026-08-13T00:00:00', new DateTimeZone('UTC')),
                'acme.rates.ecb'
            )),
            'A rate pricing another pair answers a different question.'
        );
    }

    /**
     * Build the fixture request at the requested answer shape.
     *
     * @param int    $precision Total digit budget the converted amount is expressed at.
     * @param int    $scale     Fractional digits the target currency keeps.
     * @param string $target    Uppercase ISO 4217 code the amount is to be shown in.
     */
    private static function request(int $precision = 12, int $scale = 2, string $target = 'EUR'): MoneyConversionRequest
    {
        return new MoneyConversionRequest(
            new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR'),
            $target,
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
            $precision,
            $scale,
            MoneyRoundingMode::HalfUp
        );
    }

    /**
     * Build one rate for the fixture pair, as at the given naive UTC instant.
     *
     * @param string $asAt Naive UTC instant the rate is as at.
     */
    private static function rate(string $asAt): MoneyExchangeRate
    {
        return new MoneyExchangeRate(
            'ZAR',
            'EUR',
            ExactDecimalArithmetic::fromLiteral('0.04938240'),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            'acme.rates.ecb'
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
