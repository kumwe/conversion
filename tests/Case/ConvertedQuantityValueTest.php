<?php

/**
 * Replays the App's converted-quantity contract and refusal corpus: a
 * converted quantity is unconstructible without provenance whose numbers
 * prove themselves, structurally unlike a stored quantity, byte-identical
 * through every published round trip, and refused — never partially
 * believed — when a member is missing, mistyped or contradictory. The
 * converted fixture is built by direct construction and matches the App's
 * converter output byte for byte.
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
use Kumwe\Conversion\Value\ConvertedQuantityValue;
use Kumwe\Conversion\Value\QuantityRoundingMode;
use Kumwe\Conversion\Value\QuantityValue;
use Kumwe\Conversion\Value\UnitConversionFactor;

final class ConvertedQuantityValueTest extends TestCase
{
    public function testAConvertedQuantityCarriesItsExactAndUnroundedArithmetic(): void
    {
        $converted = self::converted();

        $this->assertSame('11.339809250000', $converted->unrounded->value(), 'The unrounded product is kept.');
        $this->assertSame('11.340', $converted->converted->amount->value(), 'The figure is the rounded product.');
        $this->assertSame('kg', $converted->converted->unit, 'The figure is in the target unit.');
        $this->assertSame('lb', $converted->source->unit, 'The source stays in its own unit.');
    }

    public function testAnIncompleteOrContradictoryConvertedQuantityCannotBeConstructed(): void
    {
        $source = new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb');
        $factor = self::factor();
        $unrounded = ExactDecimalArithmetic::multiply($source->amount, $factor->factor);

        $this->refuses(
            static fn (): ConvertedQuantityValue => new ConvertedQuantityValue(
                $source,
                new QuantityValue(ExactDecimal::fromString('9999.999', 12, 3), 'kg'),
                $factor,
                QuantityRoundingMode::HalfUp,
                $unrounded
            ),
            'under its own rounding'
        );
        $this->refuses(
            static fn (): ConvertedQuantityValue => new ConvertedQuantityValue(
                $source,
                new QuantityValue(ExactDecimal::fromString('11.340', 12, 3), 'kg'),
                $factor,
                QuantityRoundingMode::HalfUp,
                ExactDecimal::fromString('1.000000000000', 20, 12)
            ),
            'the exact product'
        );
        $this->refuses(
            static fn (): ConvertedQuantityValue => new ConvertedQuantityValue(
                $source,
                new QuantityValue(ExactDecimal::fromString('11.340', 12, 3), 'g'),
                $factor,
                QuantityRoundingMode::HalfUp,
                $unrounded
            ),
            'relating its own units'
        );
    }

    public function testAConvertedQuantityIsStructurallyUnlikeAStoredOne(): void
    {
        $converted = self::converted();
        $stored = new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb');

        $this->assertSame(['amount', 'unit'], array_keys($stored->toArray()), 'The stored shape is the pair.');
        $this->assertSame(
            [],
            array_intersect(array_keys($converted->toArray()), array_keys($stored->toArray())),
            'No stored member may appear at the converted export top level.'
        );
        $this->assertTrue($converted->toArray()['converted'], 'The converted marker is unconditional.');
        $this->assertSame(
            'acme.units.trade',
            $converted->toArray()['factor']['provider'],
            'The provider identity travels in the export.'
        );
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $converted->toArray()['factor']['as_at'],
            'The as-at instant travels in the export.'
        );
        $this->assertSame('half_up', $converted->toArray()['rounding']['mode'], 'The mode travels in the export.');
        $this->assertSame(
            '11.339809250000',
            $converted->toArray()['rounding']['unrounded_amount'],
            'The unrounded product travels in the export.'
        );
    }

    public function testExportedProvenanceRoundTrips(): void
    {
        $converted = self::converted();

        $this->assertSame(
            $converted->toArray(),
            ConvertedQuantityValue::fromArray($converted->toArray())->toArray(),
            'The export shape must round trip byte for byte.'
        );
        $this->assertSame(
            '11.340 kg converted from 25.0000 lb at 0.45359237'
                . ' as at 2026-08-14T00:00:00.000000+00:00 by acme.units.trade rounded half_up'
                . ' from 11.339809250000',
            $converted->toPortableString(),
            'The portable sentence must match the App output byte for byte.'
        );
        $this->assertSame(
            $converted->toArray(),
            ConvertedQuantityValue::fromPortableString($converted->toPortableString())->toArray(),
            'The portable sentence must round trip into the same export.'
        );
        $this->assertTrue(
            ConvertedQuantityValue::isPortableString($converted->toPortableString()),
            'The complete portable form must be recognised.'
        );
        $this->assertTrue(!ConvertedQuantityValue::isPortableString('11.340'), 'A bare figure is not the form.');
        $this->assertTrue(!ConvertedQuantityValue::isPortableString('11.340 kg'), 'A pair alone is not the form.');
        $this->assertTrue(
            !ConvertedQuantityValue::isPortableString(
                'EUR 1234.56 converted from ZAR 25000.00 at 0.04938240'
                    . ' as at 2026-08-14T00:00:00.000000+00:00 by acme.rates.ecb rounded half_up'
                    . ' from 1234.5600000000'
            ),
            'The money portable form must not read as a quantity.'
        );

        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray(
                ['value' => ['amount' => '11.340', 'unit' => 'kg']]
            ),
            'exactly its declared members'
        );
        $incomplete = $converted->toArray();
        $incomplete['converted'] = false;
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($incomplete),
            'marked as converted'
        );
    }

    public function testAnExportMemberOfTheWrongTypeIsRefused(): void
    {
        foreach (['factor', 'rounding'] as $member) {
            $mistyped = self::converted()->toArray();
            $mistyped[$member] = 'half_up';
            $this->refuses(
                static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($mistyped),
                'export member has the wrong type'
            );
        }
    }

    public function testAnExportRoundingMustCarryExactTypedProvenance(): void
    {
        foreach (['mode', 'scale', 'unrounded_amount'] as $member) {
            $mistyped = self::converted()->toArray();
            $mistyped['rounding'][$member] = $member === 'scale' ? '3' : 12;
            $this->refuses(
                static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($mistyped),
                'rounding member has the wrong type'
            );

            $missing = self::converted()->toArray();
            unset($missing['rounding'][$member]);
            $this->refuses(
                static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($missing),
                'rounding must carry exactly its declared members'
            );
        }

        $extra = self::converted()->toArray();
        $extra['rounding']['precision'] = 12;
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($extra),
            'rounding must carry exactly its declared members'
        );

        $contradictory = self::converted()->toArray();
        $contradictory['rounding']['scale'] = 9;
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($contradictory),
            'rounding scale must match the converted amount'
        );
    }

    public function testAnExportFactorMustBeAKeyedDocument(): void
    {
        $positional = self::converted()->toArray();
        $positional['factor'] = array_values($positional['factor']);
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($positional),
            'must be a keyed document'
        );

        $partly = self::converted()->toArray();
        $partly['factor'][0] = 'lb';
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($partly),
            'must be a keyed document'
        );
    }

    public function testAnExportRequiresExactAmountAndUnitPairs(): void
    {
        $malformed = [
            '11.340',
            ['amount' => '11.340'],
            ['unit' => 'kg'],
            ['amount' => '11.340', 'unit' => 'kg', 'precision' => 12],
            ['amount' => 11.34, 'unit' => 'kg'],
            ['amount' => '11.340', 'unit' => 12],
        ];

        foreach (['source', 'value'] as $member) {
            foreach ($malformed as $candidate) {
                $export = self::converted()->toArray();
                $export[$member] = $candidate;
                $this->refuses(
                    static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($export),
                    'exact amount and unit pairs'
                );
            }
        }
    }

    public function testAnUnknownRoundingModeIsRefusedInBothForms(): void
    {
        $unknown = self::converted()->toArray();
        $unknown['rounding']['mode'] = 'half_odd';
        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromArray($unknown),
            'names an unknown rounding mode'
        );

        $this->refuses(
            static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromPortableString(
                str_replace(' rounded half_up ', ' rounded half_odd ', self::converted()->toPortableString())
            ),
            'names an unknown rounding mode'
        );
    }

    public function testTextThatIsNotThePortableFormIsRefusedRatherThanPartlyBelieved(): void
    {
        $converted = self::converted();
        $truncated = [
            '11.340',
            '11.340 kg',
            '11.340 kg converted from 25.0000 lb at 0.45359237',
            '11.340 kg converted from 25.0000 lb at 0.45359237 as at 2026-08-14T00:00:00.000000+00:00',
            str_replace(' from 11.339809250000', '', $converted->toPortableString()),
            str_replace(' rounded half_up ', ' rounded HALF_UP ', $converted->toPortableString()),
            str_replace(' by acme.units.trade', ' by anonymous', $converted->toPortableString()),
            $converted->toPortableString() . ' or thereabouts',
        ];

        foreach ($truncated as $candidate) {
            $this->assertTrue(
                !ConvertedQuantityValue::isPortableString($candidate),
                'Not the quantity portable form: ' . $candidate
            );
            $this->refuses(
                static fn (): ConvertedQuantityValue => ConvertedQuantityValue::fromPortableString($candidate),
                'spelled in the portable form'
            );
        }
    }

    /**
     * Build the converted quantity every assertion in this class is made against.
     */
    private static function converted(): ConvertedQuantityValue
    {
        $source = new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb');
        $factor = self::factor();
        $unrounded = ExactDecimalArithmetic::multiply($source->amount, $factor->factor);

        return new ConvertedQuantityValue(
            $source,
            new QuantityValue(
                ExactDecimalArithmetic::round($unrounded, 12, 3, QuantityRoundingMode::HalfUp),
                'kg'
            ),
            $factor,
            QuantityRoundingMode::HalfUp,
            $unrounded
        );
    }

    /**
     * Build one factor for the fixture pair, attributed to the test conversion package.
     */
    private static function factor(): UnitConversionFactor
    {
        return new UnitConversionFactor(
            'lb',
            'kg',
            ExactDecimalArithmetic::fromLiteral('0.45359237'),
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
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
