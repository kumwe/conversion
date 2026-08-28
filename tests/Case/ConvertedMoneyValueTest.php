<?php

/**
 * Replays the App's converted-money contract: a converted amount is
 * unconstructible without provenance whose numbers prove themselves,
 * structurally unlike a stored amount, and byte-identical through every
 * published round trip. The converted fixture is built by direct
 * construction — the converters land with the Contract layer — and matches
 * the App's converter output byte for byte.
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
use Kumwe\Conversion\Value\ConvertedMoneyValue;
use Kumwe\Conversion\Value\MoneyExchangeRate;
use Kumwe\Conversion\Value\MoneyRoundingMode;
use Kumwe\Conversion\Value\MoneyValue;

final class ConvertedMoneyValueTest extends TestCase
{
    public function testAConvertedAmountCarriesItsExactAndUnroundedArithmetic(): void
    {
        $converted = self::converted();

        $this->assertSame('1234.5600000000', $converted->unrounded->value(), 'The unrounded product is kept.');
        $this->assertSame('1234.56', $converted->converted->amount->value(), 'The figure is the rounded product.');
        $this->assertSame('EUR', $converted->converted->currency, 'The figure is in the quote currency.');
        $this->assertSame('ZAR', $converted->source->currency, 'The source stays in its own currency.');
    }

    public function testAnIncompleteOrContradictoryConvertedAmountCannotBeConstructed(): void
    {
        $source = new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR');
        $rate = self::rate('0.04938240');
        $unrounded = ExactDecimalArithmetic::multiply($source->amount, $rate->rate);

        $this->refuses(
            static fn (): ConvertedMoneyValue => new ConvertedMoneyValue(
                $source,
                new MoneyValue(ExactDecimal::fromString('9999.99', 12, 2), 'EUR'),
                $rate,
                MoneyRoundingMode::HalfUp,
                $unrounded
            ),
            'under its own rounding'
        );
        $this->refuses(
            static fn (): ConvertedMoneyValue => new ConvertedMoneyValue(
                $source,
                new MoneyValue(ExactDecimal::fromString('1234.56', 12, 2), 'EUR'),
                $rate,
                MoneyRoundingMode::HalfUp,
                ExactDecimal::fromString('1.000000000000', 20, 12)
            ),
            'the exact product'
        );
        $this->refuses(
            static fn (): ConvertedMoneyValue => new ConvertedMoneyValue(
                $source,
                new MoneyValue(ExactDecimal::fromString('1234.56', 12, 2), 'USD'),
                $rate,
                MoneyRoundingMode::HalfUp,
                $unrounded
            ),
            'prices its own pair'
        );
    }

    public function testAConvertedAmountIsStructurallyUnlikeAStoredOne(): void
    {
        $converted = self::converted();
        $stored = new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR');

        $this->assertSame(['amount', 'currency'], array_keys($stored->toArray()), 'The stored shape is the pair.');
        $this->assertSame(
            [],
            array_intersect(array_keys($converted->toArray()), array_keys($stored->toArray())),
            'No stored member may appear at the converted export top level.'
        );
        $this->assertTrue($converted->toArray()['converted'], 'The converted marker is unconditional.');
        $this->assertSame(
            'acme.rates.ecb',
            $converted->toArray()['rate']['provider'],
            'The provider identity travels in the export.'
        );
        $this->assertSame(
            '2026-08-14T00:00:00.000000+00:00',
            $converted->toArray()['rate']['as_at'],
            'The as-at instant travels in the export.'
        );
        $this->assertSame('half_up', $converted->toArray()['rounding']['mode'], 'The mode travels in the export.');
        $this->assertSame(
            '1234.5600000000',
            $converted->toArray()['rounding']['unrounded_amount'],
            'The unrounded product travels in the export.'
        );
    }

    public function testExportedProvenanceRoundTrips(): void
    {
        $converted = self::converted();

        $this->assertSame(
            $converted->toArray(),
            ConvertedMoneyValue::fromArray($converted->toArray())->toArray(),
            'The export shape must round trip byte for byte.'
        );
        $this->assertSame(
            'EUR 1234.56 converted from ZAR 25000.00 at 0.04938240'
                . ' as at 2026-08-14T00:00:00.000000+00:00 by acme.rates.ecb rounded half_up from 1234.5600000000',
            $converted->toPortableString(),
            'The portable sentence must match the App output byte for byte.'
        );
        $this->assertSame(
            $converted->toArray(),
            ConvertedMoneyValue::fromPortableString($converted->toPortableString())->toArray(),
            'The portable sentence must round trip into the same export.'
        );
        $this->assertTrue(
            ConvertedMoneyValue::isPortableString($converted->toPortableString()),
            'The complete portable form must be recognised.'
        );
        $this->assertTrue(!ConvertedMoneyValue::isPortableString('1234.56'), 'A bare figure is not the form.');
        $this->assertTrue(!ConvertedMoneyValue::isPortableString('EUR 1234.56'), 'A prefix alone is not the form.');

        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray(
                ['value' => ['amount' => '1234.56', 'currency' => 'EUR']]
            ),
            'exactly its declared members'
        );
        $incomplete = $converted->toArray();
        $incomplete['converted'] = false;
        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($incomplete),
            'marked as converted'
        );
    }

    public function testExportedRoundingMetadataCannotBeOmittedOrContradictTheAmount(): void
    {
        foreach (['mode', 'scale', 'unrounded_amount'] as $member) {
            $mistyped = self::converted()->toArray();
            $mistyped['rounding'][$member] = $member === 'scale' ? '2' : 12;
            $this->refuses(
                static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($mistyped),
                'rounding member has the wrong type'
            );

            $missing = self::converted()->toArray();
            unset($missing['rounding'][$member]);
            $this->refuses(
                static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($missing),
                'rounding must carry exactly its declared members'
            );
        }

        $extra = self::converted()->toArray();
        $extra['rounding']['precision'] = 12;
        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($extra),
            'rounding must carry exactly its declared members'
        );

        $contradictory = self::converted()->toArray();
        $contradictory['rounding']['scale'] = 9;
        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($contradictory),
            'rounding scale must match the converted amount'
        );
    }

    public function testAnExportMemberOfTheWrongTypeIsRefused(): void
    {
        foreach (['rate', 'rounding'] as $member) {
            $mistyped = self::converted()->toArray();
            $mistyped[$member] = 'half_up';
            $this->refuses(
                static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($mistyped),
                'export member has the wrong type'
            );
        }
    }

    public function testAnExportRateMustBeAKeyedDocument(): void
    {
        $positional = self::converted()->toArray();
        $positional['rate'] = array_values($positional['rate']);
        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($positional),
            'must be a keyed document'
        );
    }

    public function testAnExportRequiresExactAmountAndCurrencyPairs(): void
    {
        $malformed = [
            '1234.56',
            ['amount' => '1234.56'],
            ['currency' => 'EUR'],
            ['amount' => '1234.56', 'currency' => 'EUR', 'precision' => 12],
            ['amount' => 1234.56, 'currency' => 'EUR'],
            ['amount' => '1234.56', 'currency' => 12],
        ];

        foreach (['source', 'value'] as $member) {
            foreach ($malformed as $candidate) {
                $export = self::converted()->toArray();
                $export[$member] = $candidate;
                $this->refuses(
                    static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($export),
                    'exact amount and currency pairs'
                );
            }
        }
    }

    public function testAnUnknownRoundingModeIsRefusedInBothForms(): void
    {
        $unknown = self::converted()->toArray();
        $unknown['rounding']['mode'] = 'half_odd';
        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromArray($unknown),
            'names an unknown rounding mode'
        );

        $this->refuses(
            static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromPortableString(
                str_replace(' rounded half_up ', ' rounded half_odd ', self::converted()->toPortableString())
            ),
            'names an unknown rounding mode'
        );
    }

    public function testTextThatIsNotThePortableFormIsRefusedRatherThanPartlyBelieved(): void
    {
        $converted = self::converted();
        $truncated = [
            '1234.56',
            'EUR 1234.56',
            'EUR 1234.56 converted from ZAR 25000.00 at 0.04938240',
            str_replace(' from 1234.5600000000', '', $converted->toPortableString()),
            str_replace(' rounded half_up ', ' rounded HALF_UP ', $converted->toPortableString()),
            str_replace(' by acme.rates.ecb', ' by anonymous', $converted->toPortableString()),
            $converted->toPortableString() . ' or thereabouts',
            '11.340 kg converted from 25.0000 lb at 0.45359237'
                . ' as at 2026-08-14T00:00:00.000000+00:00 by acme.units.trade rounded half_up'
                . ' from 11.339809250000',
        ];

        foreach ($truncated as $candidate) {
            $this->assertTrue(
                !ConvertedMoneyValue::isPortableString($candidate),
                'Not the money portable form: ' . $candidate
            );
            $this->refuses(
                static fn (): ConvertedMoneyValue => ConvertedMoneyValue::fromPortableString($candidate),
                'spelled in the portable form'
            );
        }
    }

    public function testAConvertedAmountIsRecognisedInEitherFormAndNotMistakenForOtherData(): void
    {
        $converted = self::converted();

        $this->assertSame($converted, ConvertedMoneyValue::detect($converted), 'The object passes through.');
        $this->assertSame(
            $converted->toArray(),
            ConvertedMoneyValue::detect($converted->toArray())?->toArray(),
            'The export form is recognised and restored.'
        );

        foreach (
            [
                'a bare figure' => '1234.56',
                'nothing at all' => null,
                'a stored money pair' => ['amount' => '25000.00', 'currency' => 'ZAR'],
                'an unrelated flag' => ['converted' => true],
                'a near miss with one member short' => [
                    'converted' => true,
                    'value' => ['amount' => '1.00', 'currency' => 'EUR'],
                    'source' => ['amount' => '1.00', 'currency' => 'EUR'],
                    'rate' => [],
                ],
            ] as $case => $value
        ) {
            $this->assertSame(
                null,
                ConvertedMoneyValue::detect($value),
                $case . ' was read as a converted amount.'
            );
        }
    }

    public function testAnExportThatClaimsToBeConvertedAndCannotProveItIsRefused(): void
    {
        $broken = self::converted()->toArray();
        $broken['rate']['rate'] = '0.99999999';

        $this->assertThrows(
            static fn (): ?ConvertedMoneyValue => ConvertedMoneyValue::detect($broken),
            InvalidArgumentException::class,
            'A figure that says it is converted and cannot prove it must be refused, not rendered bare.'
        );
    }

    /**
     * Build the converted amount every assertion in this class is made against.
     */
    private static function converted(): ConvertedMoneyValue
    {
        $source = new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR');
        $rate = self::rate('0.04938240');
        $unrounded = ExactDecimalArithmetic::multiply($source->amount, $rate->rate);

        return new ConvertedMoneyValue(
            $source,
            new MoneyValue(
                ExactDecimalArithmetic::round($unrounded, 12, 2, MoneyRoundingMode::HalfUp),
                'EUR'
            ),
            $rate,
            MoneyRoundingMode::HalfUp,
            $unrounded
        );
    }

    /**
     * Build one rate for the fixture pair, attributed to the test rate package.
     *
     * @param string $rate Canonical rate literal, EUR per ZAR.
     */
    private static function rate(string $rate): MoneyExchangeRate
    {
        return new MoneyExchangeRate(
            'ZAR',
            'EUR',
            ExactDecimalArithmetic::fromLiteral($rate),
            new DateTimeImmutable('2026-08-14T00:00:00', new DateTimeZone('UTC')),
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
