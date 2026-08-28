<?php

/**
 * Proves the money pipeline's provenance carriage with the contract halves
 * of the App's provider corpus: candidates arrive only through the injected
 * catalog port in declared order, a declining provider is passed over
 * unasked, a misattributed or wrong-answer rate is refused with the reason
 * kept, two providers converting the same amount produce the same shape,
 * and the only thing the pipeline can hand back is a converted amount
 * carrying its rate, as-at instant, provider identity and declared
 * rounding.
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
use Kumwe\Conversion\Provider\MoneyConversionPipeline;
use Kumwe\Conversion\Provider\MoneyRateProvider;
use Kumwe\Conversion\Provider\MoneyRateProviderCatalog;
use Kumwe\Conversion\Provider\MoneyRateUnavailable;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\ConvertedMoneyValue;
use Kumwe\Conversion\Value\MoneyExchangeRate;
use Kumwe\Conversion\Value\MoneyRoundingMode;
use Kumwe\Conversion\Value\MoneyValue;
use LogicException;

final class MoneyConversionPipelineTest extends TestCase
{
    public function testAProviderThatDeclinesIsPassedOverWithoutBeingAskedForARate(): void
    {
        $converted = $this->pipeline(
            new ScriptedMoneyRateProvider('acme.rates.ecb', false),
            new ScriptedMoneyRateProvider(
                'zeta.treasury.contracted',
                true,
                self::rate('0.04938240', 'zeta.treasury.contracted')
            )
        )->convert(self::request());

        $this->assertSame(
            'zeta.treasury.contracted',
            $converted->rate->provider,
            'The next declared provider answers when the first declines.'
        );
        $this->assertSame('1234.56', $converted->converted->amount->value(), 'The answer is converted exactly.');
        $this->assertSame('EUR', $converted->converted->currency, 'The answer is in the target currency.');
    }

    public function testTheFirstAnsweringProviderInDeclaredOrderWins(): void
    {
        $converted = $this->pipeline(
            new ScriptedMoneyRateProvider(
                'zeta.treasury.contracted',
                true,
                self::rate('0.05000000', 'zeta.treasury.contracted')
            ),
            new ScriptedMoneyRateProvider('acme.rates.ecb', true, self::rate('0.04938240', 'acme.rates.ecb'))
        )->convert(self::request());

        $this->assertSame(
            'zeta.treasury.contracted',
            $converted->rate->provider,
            'The catalog order is the resolution order.'
        );
        $this->assertSame(
            '1250.00',
            $converted->converted->amount->value(),
            'The first answering provider\'s rate is the one applied.'
        );
    }

    public function testTwoProvidersConvertingTheSameAmountProduceTheSameShape(): void
    {
        $left = $this->pipeline(
            new ScriptedMoneyRateProvider('acme.rates.ecb', true, self::rate('0.04938240', 'acme.rates.ecb'))
        )->convert(self::request())->toArray();
        $right = $this->pipeline(
            new ScriptedMoneyRateProvider(
                'zeta.treasury.contracted',
                true,
                self::rate('0.04938240', 'zeta.treasury.contracted')
            )
        )->convert(self::request())->toArray();

        $this->assertSame(array_keys($left), array_keys($right), 'Both exports carry the same member set.');
        $this->assertSame($left['value'], $right['value'], 'The same rate produces the same figure.');
        $this->assertSame('acme.rates.ecb', $left['rate']['provider'], 'The first attribution is its own.');
        $this->assertSame(
            'zeta.treasury.contracted',
            $right['rate']['provider'],
            'The second attribution is its own.'
        );
        $this->assertTrue($left['rate'] !== $right['rate'], 'The rate members differ only in their attribution.');
    }

    public function testAConversionEveryProviderDeclinesIsRefusedRatherThanInvented(): void
    {
        $pipeline = $this->pipeline(
            new ScriptedMoneyRateProvider('acme.rates.ecb', false),
            new ScriptedMoneyRateProvider('zeta.treasury.contracted', false)
        );

        $error = $this->assertThrows(
            static fn (): ConvertedMoneyValue => $pipeline->convert(self::request()),
            MoneyRateUnavailable::class,
            'Every provider declining must refuse, not invent.'
        );
        $this->assertSame(
            'No contributed rate provider can price this conversion.',
            $error->getMessage(),
            'The refusal must be the stated no-provider outcome.'
        );
    }

    public function testAnEmptyCatalogRefusesTheConversion(): void
    {
        $pipeline = $this->pipeline();

        $error = $this->assertThrows(
            static fn (): ConvertedMoneyValue => $pipeline->convert(self::request()),
            MoneyRateUnavailable::class,
            'A catalog offering no provider must refuse the conversion.'
        );
        $this->assertSame(
            'No contributed rate provider can price this conversion.',
            $error->getMessage(),
            'An installation with no rate package refuses with the stated outcome.'
        );
    }

    public function testARateAttributedToAnotherProviderIsRefused(): void
    {
        $pipeline = $this->pipeline(new ScriptedMoneyRateProvider(
            'acme.rates.ecb',
            true,
            self::rate('0.04938240', 'zeta.treasury.contracted')
        ));

        $error = $this->assertThrows(
            static fn (): ConvertedMoneyValue => $pipeline->convert(self::request()),
            MoneyRateUnavailable::class,
            'A misattributed rate must be refused, not laundered.'
        );
        $this->assertStringContains(
            'attributed to another provider',
            $error->getMessage(),
            'The refusal must name the misattribution.'
        );
    }

    public function testARateDatedAfterTheInstantAskedAboutIsRefusedRatherThanConvertedWith(): void
    {
        $this->refuses($this->pipeline(new ScriptedMoneyRateProvider(
            'acme.rates.ecb',
            true,
            self::rate('0.04938240', 'acme.rates.ecb', '2026-08-15T00:00:00')
        )));
    }

    public function testARatePricingAnotherPairIsRefusedRatherThanConvertedWith(): void
    {
        $this->refuses($this->pipeline(new ScriptedMoneyRateProvider(
            'acme.rates.ecb',
            true,
            self::rate('0.04938240', 'acme.rates.ecb', '2026-08-14T00:00:00', 'USD')
        )));
    }

    public function testAProviderThatAcceptsAndThenCannotSourceARatePropagatesItsRefusal(): void
    {
        $pipeline = $this->pipeline(new ThrowingMoneyRateProvider());

        $error = $this->assertThrows(
            static fn (): ConvertedMoneyValue => $pipeline->convert(self::request()),
            MoneyRateUnavailable::class,
            'A sourcing failure after acceptance must surface as the typed refusal.'
        );
        $this->assertSame(
            'The rate feed holds no rate for this pair.',
            $error->getMessage(),
            'The provider\'s own refusal must reach the caller unchanged.'
        );
    }

    public function testTheOnlyAnswerThePipelineCanGiveCarriesItsWholeProvenance(): void
    {
        $exported = $this->pipeline(
            new ScriptedMoneyRateProvider('acme.rates.ecb', true, self::rate('0.04938240', 'acme.rates.ecb'))
        )->convert(self::request())->toArray();

        $this->assertTrue($exported['converted'], 'The pipeline result is always marked as converted.');
        $this->assertSame('acme.rates.ecb', $exported['rate']['provider'], 'Provider identity is carried.');
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $exported['rate']['as_at'],
            'The as-at instant is carried.'
        );
        $this->assertSame('half_up', $exported['rounding']['mode'], 'The declared rounding is carried.');
        $this->assertSame(
            '1234.5600000000',
            $exported['rounding']['unrounded_amount'],
            'The unrounded exact product is carried.'
        );
    }

    /**
     * Require the pipeline to refuse the conversion and to keep the reason the rate was unusable.
     *
     * @param MoneyConversionPipeline $pipeline Pipeline whose single provider answers badly.
     */
    private function refuses(MoneyConversionPipeline $pipeline): void
    {
        $error = $this->assertThrows(
            static fn (): ConvertedMoneyValue => $pipeline->convert(self::request()),
            MoneyRateUnavailable::class,
            'An unusable rate reached a surface as though it had been converted with.'
        );
        $this->assertStringContains(
            'does not answer the conversion requested',
            $error->getMessage(),
            'The refusal must say the answer did not answer.'
        );
        $previous = $error->getPrevious();
        $this->assertTrue(
            $previous instanceof InvalidArgumentException,
            'The original refusal must be kept as the cause.'
        );
        $this->assertStringContains(
            'must price the requested pair as at the instant asked about',
            $previous->getMessage(),
            'The cause must name the rule the provider broke.'
        );
    }

    /**
     * Compose the pipeline over a fixed list of providers, in the order the catalog would offer them.
     *
     * @param MoneyRateProvider ...$providers Providers entitled to answer, in resolution order.
     */
    private function pipeline(MoneyRateProvider ...$providers): MoneyConversionPipeline
    {
        return new MoneyConversionPipeline(
            new MoneyConverter(),
            new ScriptedMoneyRateProviderCatalog(array_values($providers))
        );
    }

    /**
     * Build one rate a stand-in provider offers for the fixture conversion.
     *
     * @param string $rate     Canonical rate literal, quote per one base.
     * @param string $provider Identity the rate is attributed to.
     * @param string $asAt     Naive UTC instant the rate is as at.
     * @param string $quote    Uppercase ISO 4217 code the rate converts into.
     */
    private static function rate(
        string $rate,
        string $provider,
        string $asAt = '2026-08-14T00:00:00',
        string $quote = 'EUR'
    ): MoneyExchangeRate {
        return new MoneyExchangeRate(
            'ZAR',
            $quote,
            ExactDecimalArithmetic::fromLiteral($rate),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            $provider
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
}

/**
 * The providers one conversion is offered to, stated outright instead of resolved from a registry.
 *
 * @since 0.1.0
 */
final readonly class ScriptedMoneyRateProviderCatalog implements MoneyRateProviderCatalog
{
    /**
     * Hold the providers this catalog offers, already in resolution order.
     *
     * @param list<MoneyRateProvider> $providers Providers entitled to answer any conversion.
     */
    public function __construct(private array $providers)
    {
    }

    /**
     * Offer the same providers for every conversion, in the order they were given.
     *
     * @param MoneyConversionRequest $request Conversion a caller is looking for a rate for.
     *
     * @return list<MoneyRateProvider> The providers this catalog was composed with.
     */
    public function providersFor(MoneyConversionRequest $request): array
    {
        return $this->providers;
    }
}

/**
 * A rate provider whose answer to both questions the pipeline asks is decided in advance.
 *
 * @since 0.1.0
 */
final readonly class ScriptedMoneyRateProvider implements MoneyRateProvider
{
    /**
     * Hold the identity, the decision, and the rate this provider is scripted to answer with.
     *
     * @param string             $identifier Identity this provider is registered under.
     * @param bool               $answers    Whether it accepts the conversion it is offered.
     * @param ?MoneyExchangeRate $rate       Rate it hands back; absent when it declines.
     */
    public function __construct(
        private string $identifier,
        private bool $answers,
        private ?MoneyExchangeRate $rate = null,
    ) {
    }

    /**
     * Return the identity this provider is registered and attributed under.
     */
    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * Answer the scripted decision, whatever the conversion offered.
     *
     * @param MoneyConversionRequest $request Conversion being offered.
     */
    public function supports(MoneyConversionRequest $request): bool
    {
        return $this->answers;
    }

    /**
     * Hand back the scripted rate, or refuse to have been asked at all.
     *
     * @param MoneyConversionRequest $request Conversion being answered.
     */
    public function rateFor(MoneyConversionRequest $request): MoneyExchangeRate
    {
        return $this->rate
            ?? throw new LogicException('A provider that declined the conversion was asked for a rate.');
    }
}

/**
 * A provider that accepts every conversion and then cannot source a rate for it.
 *
 * @since 0.1.0
 */
final readonly class ThrowingMoneyRateProvider implements MoneyRateProvider
{
    /**
     * Return the identity this provider is registered and attributed under.
     */
    public function identifier(): string
    {
        return 'acme.rates.ecb';
    }

    /**
     * Accept every conversion offered.
     *
     * @param MoneyConversionRequest $request Conversion being offered.
     */
    public function supports(MoneyConversionRequest $request): bool
    {
        return true;
    }

    /**
     * Refuse with the typed sourcing failure the port documents.
     *
     * @param MoneyConversionRequest $request Conversion being answered.
     */
    public function rateFor(MoneyConversionRequest $request): MoneyExchangeRate
    {
        throw new MoneyRateUnavailable('The rate feed holds no rate for this pair.');
    }
}
