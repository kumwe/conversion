<?php

/**
 * Proves what a stored quantity value admits and refuses, and the export
 * shape it is carried in.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\QuantityValue;

final class QuantityValueTest extends TestCase
{
    public function testAnAmountBindsToItsUnitAndExportsTheCanonicalPair(): void
    {
        $value = new QuantityValue(ExactDecimal::fromString('25.0000', 12, 4), 'lb');

        $this->assertSame('lb', $value->unit, 'The unit is carried as given.');
        $this->assertSame('25.0000', $value->amount->value(), 'The amount stays the canonical literal.');
        $this->assertSame(
            ['amount' => '25.0000', 'unit' => 'lb'],
            $value->toArray(),
            'The export is exactly the amount-and-unit pair, in that order.'
        );
    }

    public function testTheFullPortableIdentifierGrammarIsAdmitted(): void
    {
        foreach (['kg', 'm/s', 'kg.case_10-x', str_repeat('u', 63), '9'] as $unit) {
            $this->assertSame(
                $unit,
                (new QuantityValue(ExactDecimal::fromString('1.00', 12, 2), $unit))->unit,
                'The unit "' . $unit . '" is a bounded portable identifier and must be admitted.'
            );
        }
    }

    public function testAUnitThatIsNotABoundedPortableIdentifierIsRefused(): void
    {
        foreach (['', 'metric tonne', str_repeat('u', 64), '-kg', '.kg', 'kg€'] as $unit) {
            $error = $this->assertThrows(
                static fn (): QuantityValue => new QuantityValue(ExactDecimal::fromString('1.00', 12, 2), $unit),
                InvalidArgumentException::class,
                'The unit "' . $unit . '" must be refused.'
            );
            $this->assertStringContains(
                'bounded portable identifier',
                $error->getMessage(),
                'The refusal must name the identifier rule.'
            );
        }
    }
}
