<?php

/**
 * Replays the App's UnitConversionPipelineRefusalTest and proves the
 * pipeline's provenance carriage: candidates arrive only through the
 * injected catalog port, a declining provider is passed over unasked, a
 * provider that answers badly is refused with the original reason kept as
 * the cause, and the only thing the pipeline can hand back is a converted
 * quantity carrying its factor, as-at instant, provider identity and
 * declared rounding.
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
use Kumwe\Conversion\Provider\UnitConversionPipeline;
use Kumwe\Conversion\Provider\UnitConversionProvider;
use Kumwe\Conversion\Provider\UnitConversionProviderCatalog;
use Kumwe\Conversion\Provider\UnitConversionUnavailable;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\ConvertedQuantityValue;
use Kumwe\Conversion\Value\QuantityRoundingMode;
use Kumwe\Conversion\Value\QuantityValue;
use Kumwe\Conversion\Value\UnitConversionFactor;
use LogicException;

final class UnitConversionPipelineTest extends TestCase
{
    public function testAProviderThatDeclinesIsPassedOverWithoutBeingAskedForAFactor(): void
    {
        $converted = $this->pipeline(
            new ScriptedUnitConversionProvider('acme.units.trade', false),
            new ScriptedUnitConversionProvider(
                'zeta.logistics.packing',
                true,
                self::factor('12.000000', 'zeta.logistics.packing')
            )
        )->convert(self::request());

        $this->assertSame(
            'zeta.logistics.packing',
            $converted->factor->provider,
            'The next declared provider answers when the first declines.'
        );
        $this->assertSame('24.000000', $converted->converted->amount->value(), 'The answer is converted exactly.');
        $this->assertSame('unit', $converted->converted->unit, 'The answer is in the target unit.');
    }

    public function testAConversionEveryProviderDeclinesIsRefusedRatherThanInvented(): void
    {
        $pipeline = $this->pipeline(
            new ScriptedUnitConversionProvider('acme.units.trade', false),
            new ScriptedUnitConversionProvider('zeta.logistics.packing', false)
        );

        $error = $this->assertThrows(
            static fn (): ConvertedQuantityValue => $pipeline->convert(self::request()),
            UnitConversionUnavailable::class,
            'Every provider declining must refuse, not invent.'
        );
        $this->assertSame(
            'No contributed conversion provider can relate these units.',
            $error->getMessage(),
            'The refusal must be the stated no-provider outcome.'
        );
    }

    public function testAnEmptyCatalogRefusesTheConversion(): void
    {
        $pipeline = $this->pipeline();

        $error = $this->assertThrows(
            static fn (): ConvertedQuantityValue => $pipeline->convert(self::request()),
            UnitConversionUnavailable::class,
            'A catalog offering no provider must refuse the conversion.'
        );
        $this->assertSame(
            'No contributed conversion provider can relate these units.',
            $error->getMessage(),
            'An installation with no conversion package refuses with the stated outcome.'
        );
    }

    public function testAFactorDatedAfterTheInstantAskedAboutIsRefusedRatherThanConvertedWith(): void
    {
        $pipeline = $this->pipeline(new ScriptedUnitConversionProvider(
            'acme.units.trade',
            true,
            self::factor('12.000000', 'acme.units.trade', '2026-08-15T00:00:00')
        ));

        $this->refuses($pipeline);
    }

    public function testAFactorRelatingOtherUnitsIsRefusedRatherThanConvertedWith(): void
    {
        $pipeline = $this->pipeline(new ScriptedUnitConversionProvider(
            'acme.units.trade',
            true,
            self::factor('12.000000', 'acme.units.trade', '2026-08-14T00:00:00', 'pallet')
        ));

        $this->refuses($pipeline);
    }

    public function testAFactorAttributedToAnotherProviderIsRefused(): void
    {
        $pipeline = $this->pipeline(new ScriptedUnitConversionProvider(
            'acme.units.trade',
            true,
            self::factor('12.000000', 'zeta.logistics.packing')
        ));

        $error = $this->assertThrows(
            static fn (): ConvertedQuantityValue => $pipeline->convert(self::request()),
            UnitConversionUnavailable::class,
            'A misattributed factor must be refused, not laundered.'
        );
        $this->assertStringContains(
            'attributed to another provider',
            $error->getMessage(),
            'The refusal must name the misattribution.'
        );
    }

    public function testAProviderThatAcceptsAndThenCannotSourceAFactorPropagatesItsRefusal(): void
    {
        $pipeline = $this->pipeline(new ThrowingUnitConversionProvider());

        $error = $this->assertThrows(
            static fn (): ConvertedQuantityValue => $pipeline->convert(self::request()),
            UnitConversionUnavailable::class,
            'A sourcing failure after acceptance must surface as the typed refusal.'
        );
        $this->assertSame(
            'The trade table holds no factor for this pair.',
            $error->getMessage(),
            'The provider\'s own refusal must reach the caller unchanged.'
        );
    }

    public function testTheOnlyAnswerThePipelineCanGiveCarriesItsWholeProvenance(): void
    {
        $exported = $this->pipeline(new ScriptedUnitConversionProvider(
            'acme.units.trade',
            true,
            self::factor('12.000000', 'acme.units.trade')
        ))->convert(self::request())->toArray();

        $this->assertTrue($exported['converted'], 'The pipeline result is always marked as converted.');
        $this->assertSame('acme.units.trade', $exported['factor']['provider'], 'Provider identity is carried.');
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $exported['factor']['as_at'],
            'The as-at instant is carried.'
        );
        $this->assertSame('half_up', $exported['rounding']['mode'], 'The declared rounding is carried.');
        $this->assertSame(
            '24.000000000000',
            $exported['rounding']['unrounded_amount'],
            'The unrounded exact product is carried.'
        );
    }

    /**
     * Require the pipeline to refuse the conversion and to keep the reason the factor was unusable.
     *
     * @param UnitConversionPipeline $pipeline Pipeline whose single provider answers badly.
     */
    private function refuses(UnitConversionPipeline $pipeline): void
    {
        $error = $this->assertThrows(
            static fn (): ConvertedQuantityValue => $pipeline->convert(self::request()),
            UnitConversionUnavailable::class,
            'An unusable factor reached a surface as though it had been converted with.'
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
            'must relate the requested units as at the instant asked about',
            $previous->getMessage(),
            'The cause must name the rule the provider broke.'
        );
    }

    /**
     * Compose the pipeline over a fixed list of providers, in the order the catalog would offer them.
     *
     * @param UnitConversionProvider ...$providers Providers entitled to answer, in resolution order.
     */
    private function pipeline(UnitConversionProvider ...$providers): UnitConversionPipeline
    {
        return new UnitConversionPipeline(
            new QuantityConverter(),
            new ScriptedUnitConversionProviderCatalog(array_values($providers))
        );
    }

    /**
     * Build one factor a stand-in provider offers for the fixture conversion.
     *
     * @param string $factor   Canonical factor literal, target units per one case.
     * @param string $provider Identity the factor is attributed to.
     * @param string $asAt     Naive UTC instant the factor is as at.
     * @param string $target   Portable identifier of the unit the factor converts into.
     */
    private static function factor(
        string $factor,
        string $provider,
        string $asAt = '2026-08-14T00:00:00',
        string $target = 'unit'
    ): UnitConversionFactor {
        return new UnitConversionFactor(
            'case',
            $target,
            ExactDecimalArithmetic::fromLiteral($factor),
            new DateTimeImmutable($asAt, new DateTimeZone('UTC')),
            $provider
        );
    }

    /**
     * The one conversion every case in this class asks for.
     */
    private static function request(): UnitConversionRequest
    {
        return new UnitConversionRequest(
            new QuantityValue(ExactDecimal::fromString('2.000000', 12, 6), 'case'),
            'unit',
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
            12,
            6,
            QuantityRoundingMode::HalfUp
        );
    }
}

/**
 * The providers one conversion is offered to, stated outright instead of resolved from a registry.
 *
 * @since 0.1.0
 */
final readonly class ScriptedUnitConversionProviderCatalog implements UnitConversionProviderCatalog
{
    /**
     * Hold the providers this catalog offers, already in resolution order.
     *
     * @param list<UnitConversionProvider> $providers Providers entitled to answer any conversion.
     */
    public function __construct(private array $providers)
    {
    }

    /**
     * Offer the same providers for every conversion, in the order they were given.
     *
     * @param UnitConversionRequest $request Conversion a caller is looking for a factor for.
     *
     * @return list<UnitConversionProvider> The providers this catalog was composed with.
     */
    public function providersFor(UnitConversionRequest $request): array
    {
        return $this->providers;
    }
}

/**
 * A conversion provider whose answer to both questions the pipeline asks is decided in advance.
 *
 * @since 0.1.0
 */
final readonly class ScriptedUnitConversionProvider implements UnitConversionProvider
{
    /**
     * Hold the identity, the decision, and the factor this provider is scripted to answer with.
     *
     * @param string                $identifier Identity this provider is registered under.
     * @param bool                  $answers    Whether it accepts the conversion it is offered.
     * @param ?UnitConversionFactor $factor     Factor it hands back; absent when it declines.
     */
    public function __construct(
        private string $identifier,
        private bool $answers,
        private ?UnitConversionFactor $factor = null,
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
     * @param UnitConversionRequest $request Conversion being offered.
     */
    public function supports(UnitConversionRequest $request): bool
    {
        return $this->answers;
    }

    /**
     * Hand back the scripted factor, or refuse to have been asked at all.
     *
     * @param UnitConversionRequest $request Conversion being answered.
     */
    public function factorFor(UnitConversionRequest $request): UnitConversionFactor
    {
        return $this->factor
            ?? throw new LogicException('A provider that declined the conversion was asked for a factor.');
    }
}

/**
 * A provider that accepts every conversion and then cannot source a factor for it.
 *
 * @since 0.1.0
 */
final readonly class ThrowingUnitConversionProvider implements UnitConversionProvider
{
    /**
     * Return the identity this provider is registered and attributed under.
     */
    public function identifier(): string
    {
        return 'acme.units.trade';
    }

    /**
     * Accept every conversion offered.
     *
     * @param UnitConversionRequest $request Conversion being offered.
     */
    public function supports(UnitConversionRequest $request): bool
    {
        return true;
    }

    /**
     * Refuse with the typed sourcing failure the port documents.
     *
     * @param UnitConversionRequest $request Conversion being answered.
     */
    public function factorFor(UnitConversionRequest $request): UnitConversionFactor
    {
        throw new UnitConversionUnavailable('The trade table holds no factor for this pair.');
    }
}
