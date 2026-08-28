<?php

/**
 * Proves what a stored money value admits and refuses, and the export shape
 * it is carried in.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\MoneyValue;

final class MoneyValueTest extends TestCase
{
    public function testAnAmountBindsToItsCurrencyAndExportsTheCanonicalPair(): void
    {
        $value = new MoneyValue(ExactDecimal::fromString('25000.00', 12, 2), 'ZAR');

        $this->assertSame('ZAR', $value->currency, 'The currency is carried as given.');
        $this->assertSame('25000.00', $value->amount->value(), 'The amount stays the canonical literal.');
        $this->assertSame(
            ['amount' => '25000.00', 'currency' => 'ZAR'],
            $value->toArray(),
            'The export is exactly the amount-and-currency pair, in that order.'
        );
    }

    public function testACurrencyThatIsNotAnUppercaseIsoCodeIsRefused(): void
    {
        foreach (['eur', 'ZA', 'ZARR', 'ZA1', 'ZA ', ''] as $currency) {
            $error = $this->assertThrows(
                static fn (): MoneyValue => new MoneyValue(ExactDecimal::fromString('1.00', 12, 2), $currency),
                InvalidArgumentException::class,
                'The currency "' . $currency . '" must be refused.'
            );
            $this->assertStringContains(
                'uppercase ISO 4217 code',
                $error->getMessage(),
                'The refusal must name the currency rule.'
            );
        }
    }
}
