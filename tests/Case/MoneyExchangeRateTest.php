<?php

/**
 * Proves an exchange rate is never an anonymous number: what its constructor
 * admits, every documented refusal, and the export round trip its
 * provenance travels in.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\MoneyExchangeRate;

final class MoneyExchangeRateTest extends TestCase
{
    public function testARateBindsItsPairInstantAndProviderAndExportsThemExactly(): void
    {
        $rate = self::rate();

        $this->assertSame(
            [
                'base_currency' => 'ZAR',
                'quote_currency' => 'EUR',
                'rate' => '0.04938240',
                'as_at' => '2026-08-14T00:00:00.000000+00:00',
                'provider' => 'acme.rates.ecb',
            ],
            $rate->toArray(),
            'The export carries the pair, the exact literal, the UTC instant and the provider.'
        );
        $this->assertSame(
            $rate->toArray(),
            MoneyExchangeRate::fromArray($rate->toArray())->toArray(),
            'The export must round trip byte for byte.'
        );
    }

    public function testACurrencyThatIsNotAnUppercaseIsoCodeIsRefused(): void
    {
        foreach ([['zar', 'EUR'], ['ZAR', 'eur'], ['ZA', 'EUR'], ['ZAR', 'EURO']] as $pair) {
            $this->refuses(
                static fn (): MoneyExchangeRate => self::rate(base: $pair[0], quote: $pair[1]),
                'uppercase ISO 4217 code'
            );
        }
    }

    public function testARateMustPriceTwoDifferentCurrencies(): void
    {
        $this->refuses(
            static fn (): MoneyExchangeRate => self::rate(base: 'EUR', quote: 'EUR'),
            'must price two different currencies'
        );
    }

    public function testARateMustBeAnExactValueAboveZeroWithAFraction(): void
    {
        foreach (['0.00000000', '-0.04938240', '0.0'] as $literal) {
            $this->refuses(
                static fn (): MoneyExchangeRate => self::rate($literal),
                'above zero'
            );
        }
        $this->refuses(
            static fn (): MoneyExchangeRate => new MoneyExchangeRate(
                'ZAR',
                'EUR',
                ExactDecimal::fromString('5', 10, 0),
                self::instant('2026-08-14T00:00:00'),
                'acme.rates.ecb'
            ),
            'above zero'
        );
    }

    public function testAnAsAtInstantOutsideUtcIsRefused(): void
    {
        $this->refuses(
            static fn (): MoneyExchangeRate => new MoneyExchangeRate(
                'ZAR',
                'EUR',
                ExactDecimalArithmetic::fromLiteral('0.04938240'),
                new DateTimeImmutable('2026-08-14T02:00:00', new DateTimeZone('Africa/Windhoek')),
                'acme.rates.ecb'
            ),
            'expressed in UTC'
        );
    }

    public function testAProviderThatIsNotANamespacedIdentifierIsRefused(): void
    {
        foreach (['anonymous', 'Acme.rates', 'acme.', '.ecb', 'acme..ecb'] as $provider) {
            $this->refuses(
                static fn (): MoneyExchangeRate => self::rate(provider: $provider),
                'namespaced identifier'
            );
        }
    }

    public function testAnExportMissingOrPaddingAMemberIsRefused(): void
    {
        $exported = self::rate()->toArray();

        foreach (array_keys($exported) as $member) {
            $missing = $exported;
            unset($missing[$member]);
            $this->refuses(
                static fn (): MoneyExchangeRate => MoneyExchangeRate::fromArray($missing),
                'exactly its declared members'
            );
        }
        $this->refuses(
            static fn (): MoneyExchangeRate => MoneyExchangeRate::fromArray($exported + ['base' => 'ZAR']),
            'exactly its declared members'
        );
    }

    public function testAnExportMemberOfTheWrongTypeIsRefused(): void
    {
        $exported = self::rate()->toArray();

        foreach (array_keys($exported) as $member) {
            $mistyped = $exported;
            $mistyped[$member] = 12;
            $this->refuses(
                static fn (): MoneyExchangeRate => MoneyExchangeRate::fromArray($mistyped),
                'member has the wrong type'
            );
        }
    }

    public function testAnAsAtInstantIsReadBackOnlyFromItsCanonicalSpelling(): void
    {
        $spellings = [
            '2026-08-14 00:00:00',
            '2026-08-14T00:00:00+00:00',
            '2026-08-14T00:00:00.000000Z',
            '2026-02-30T00:00:00.000000+00:00',
            '',
        ];

        foreach ($spellings as $spelling) {
            $this->refuses(
                static fn (): DateTimeImmutable => MoneyExchangeRate::instant($spelling),
                'not a canonical UTC instant'
            );
            $mistyped = self::rate()->toArray();
            $mistyped['as_at'] = $spelling;
            $this->refuses(
                static fn (): MoneyExchangeRate => MoneyExchangeRate::fromArray($mistyped),
                'not a canonical UTC instant'
            );
        }

        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            MoneyExchangeRate::instant('2026-08-14T00:00:00.000000+00:00')
                ->format(MoneyExchangeRate::INSTANT_FORMAT),
            'The canonical spelling must parse back to itself.'
        );
    }

    /**
     * Build one rate for the fixture pair, attributed to the test rate package.
     *
     * @param string $rate     Canonical rate literal, quote per one base.
     * @param string $base     Uppercase ISO 4217 code the rate converts from.
     * @param string $quote    Uppercase ISO 4217 code the rate converts into.
     * @param string $provider Identity the rate is attributed to.
     */
    private static function rate(
        string $rate = '0.04938240',
        string $base = 'ZAR',
        string $quote = 'EUR',
        string $provider = 'acme.rates.ecb'
    ): MoneyExchangeRate {
        return new MoneyExchangeRate(
            $base,
            $quote,
            ExactDecimalArithmetic::fromLiteral($rate),
            self::instant('2026-08-14T00:00:00'),
            $provider
        );
    }

    /**
     * Read one naive instant as UTC, which is the only spelling the contract admits.
     *
     * @param string $value Instant without an offset.
     */
    private static function instant(string $value): DateTimeImmutable
    {
        return new DateTimeImmutable($value, new DateTimeZone('UTC'));
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
