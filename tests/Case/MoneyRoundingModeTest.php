<?php

/**
 * Replays the App's money rounding table: every declared mode produces its
 * own answer at the tie boundary, and the case set the pinned extension API
 * fixtures enumerate stays exactly as published.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\MoneyRoundingMode;

final class MoneyRoundingModeTest extends TestCase
{
    public function testEveryDeclaredRoundingModeProducesItsOwnAnswer(): void
    {
        $expected = [
            'half_up' => ['1.24', '-1.24'],
            'half_down' => ['1.23', '-1.23'],
            'half_even' => ['1.24', '-1.24'],
            'ceiling' => ['1.24', '-1.23'],
            'floor' => ['1.23', '-1.24'],
            'truncate' => ['1.23', '-1.23'],
        ];

        foreach (MoneyRoundingMode::cases() as $mode) {
            $positive = ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2350', 8, 4),
                8,
                2,
                $mode
            );
            $negative = ExactDecimalArithmetic::round(
                ExactDecimal::fromString('-1.2350', 8, 4),
                8,
                2,
                $mode
            );
            $this->assertSame($expected[$mode->value][0], $positive->value(), $mode->value . ' positive tie.');
            $this->assertSame($expected[$mode->value][1], $negative->value(), $mode->value . ' negative tie.');
        }

        $this->assertSame(
            '1.22',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('1.2250', 8, 4),
                8,
                2,
                MoneyRoundingMode::HalfEven
            )->value(),
            'An even-neighbour tie must round down under half_even.'
        );
        $this->assertSame(
            '10.0',
            ExactDecimalArithmetic::round(
                ExactDecimal::fromString('9.99', 8, 2),
                8,
                1,
                MoneyRoundingMode::HalfUp
            )->value(),
            'A rounding carry must propagate into the integer digits.'
        );
    }

    public function testTheDeclaredVocabularyIsExactlyThePublishedCaseSet(): void
    {
        $this->assertSame(
            ['half_up', 'half_down', 'half_even', 'ceiling', 'floor', 'truncate'],
            array_map(static fn (MoneyRoundingMode $mode): string => $mode->value, MoneyRoundingMode::cases()),
            'The backed case set is pinned extension API and must not move.'
        );
        $this->assertSame(
            MoneyRoundingMode::HalfUp,
            MoneyRoundingMode::from('half_up'),
            'A payload value must resolve to its declared case.'
        );
        $this->assertSame(
            null,
            MoneyRoundingMode::tryFrom('half_odd'),
            'An unknown payload value must resolve to no case.'
        );
    }
}
