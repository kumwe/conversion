<?php

/**
 * Replays the App's factor refusal corpus: a conversion factor is never an
 * anonymous number, its export carries exactly its declared members, and
 * its as-at instant reads back only from the canonical spelling.
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
use Kumwe\Conversion\Value\UnitConversionFactor;

final class UnitConversionFactorTest extends TestCase
{
    public function testAFactorBindsItsUnitsInstantAndProviderAndExportsThemExactly(): void
    {
        $factor = self::factor();

        $this->assertSame(
            [
                'source_unit' => 'lb',
                'target_unit' => 'kg',
                'factor' => '0.45359237',
                'as_at' => '2026-08-14T00:00:00.000000+00:00',
                'provider' => 'acme.units.trade',
            ],
            $factor->toArray(),
            'The export carries the units, the exact literal, the UTC instant and the provider.'
        );
        $this->assertSame(
            $factor->toArray(),
            UnitConversionFactor::fromArray($factor->toArray())->toArray(),
            'The export must round trip byte for byte.'
        );
    }

    public function testAFactorMustRelateTwoDifferentBoundedUnits(): void
    {
        foreach ([['metric tonne', 'kg'], ['lb', 'metric tonne'], ['', 'kg'], ['lb', str_repeat('u', 64)]] as $pair) {
            $this->refuses(
                static fn (): UnitConversionFactor => self::factor($pair[0], $pair[1]),
                'bounded portable identifier'
            );
        }

        $this->refuses(
            static fn (): UnitConversionFactor => self::factor('kg', 'kg'),
            'two different units'
        );
    }

    public function testAFactorMustBeAnExactValueAboveZeroWithAFraction(): void
    {
        $this->refuses(
            static fn (): UnitConversionFactor => new UnitConversionFactor(
                'lb',
                'kg',
                ExactDecimal::fromString('0.00000000', 12, 8),
                self::instant('2026-08-14T00:00:00'),
                'acme.units.trade'
            ),
            'above zero'
        );
        $this->refuses(
            static fn (): UnitConversionFactor => new UnitConversionFactor(
                'lb',
                'kg',
                ExactDecimal::fromString('-0.45359237', 12, 8),
                self::instant('2026-08-14T00:00:00'),
                'acme.units.trade'
            ),
            'above zero'
        );
        $this->refuses(
            static fn (): UnitConversionFactor => new UnitConversionFactor(
                'lb',
                'kg',
                ExactDecimal::fromString('12', 12, 0),
                self::instant('2026-08-14T00:00:00'),
                'acme.units.trade'
            ),
            'above zero'
        );
    }

    public function testAnAsAtInstantOutsideUtcIsRefused(): void
    {
        $this->refuses(
            static fn (): UnitConversionFactor => new UnitConversionFactor(
                'lb',
                'kg',
                ExactDecimalArithmetic::fromLiteral('0.45359237'),
                new DateTimeImmutable('2026-08-14T02:00:00', new DateTimeZone('Africa/Windhoek')),
                'acme.units.trade'
            ),
            'expressed in UTC'
        );
    }

    public function testAProviderThatIsNotANamespacedIdentifierIsRefused(): void
    {
        foreach (['anonymous', 'Acme.units', 'acme.', '.trade'] as $provider) {
            $this->refuses(
                static fn (): UnitConversionFactor => new UnitConversionFactor(
                    'lb',
                    'kg',
                    ExactDecimalArithmetic::fromLiteral('0.45359237'),
                    self::instant('2026-08-14T00:00:00'),
                    $provider
                ),
                'namespaced identifier'
            );
        }
    }

    public function testAFactorExportMustCarryExactlyItsDeclaredMembers(): void
    {
        $exported = self::factor()->toArray();

        foreach (array_keys($exported) as $member) {
            $missing = $exported;
            unset($missing[$member]);
            $this->refuses(
                static fn (): UnitConversionFactor => UnitConversionFactor::fromArray($missing),
                'exactly its declared members'
            );
        }

        $this->refuses(
            static fn (): UnitConversionFactor => UnitConversionFactor::fromArray(
                $exported + ['base_unit' => 'kg']
            ),
            'exactly its declared members'
        );
    }

    public function testAFactorExportMemberOfTheWrongTypeIsRefused(): void
    {
        $exported = self::factor()->toArray();

        foreach (array_keys($exported) as $member) {
            $mistyped = $exported;
            $mistyped[$member] = 12;
            $this->refuses(
                static fn (): UnitConversionFactor => UnitConversionFactor::fromArray($mistyped),
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
                static fn (): DateTimeImmutable => UnitConversionFactor::instant($spelling),
                'not a canonical UTC instant'
            );
            $mistyped = self::factor()->toArray();
            $mistyped['as_at'] = $spelling;
            $this->refuses(
                static fn (): UnitConversionFactor => UnitConversionFactor::fromArray($mistyped),
                'not a canonical UTC instant'
            );
        }

        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            UnitConversionFactor::instant('2026-08-14T00:00:00.000000+00:00')
                ->format(UnitConversionFactor::INSTANT_FORMAT),
            'The canonical spelling must parse back to itself.'
        );
    }

    /**
     * Build one factor for the fixture pair, attributed to the test conversion package.
     *
     * @param string $source Portable identifier of the unit the factor converts from.
     * @param string $target Portable identifier of the unit the factor converts into.
     */
    private static function factor(string $source = 'lb', string $target = 'kg'): UnitConversionFactor
    {
        return new UnitConversionFactor(
            $source,
            $target,
            ExactDecimalArithmetic::fromLiteral('0.45359237'),
            self::instant('2026-08-14T00:00:00'),
            'acme.units.trade'
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
