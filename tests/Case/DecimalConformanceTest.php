<?php

/** Replay the language-neutral decimal contract without a host application. @since 0.1.3 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use InvalidArgumentException;
use Kumwe\Conversion\Decimal\ExactDecimal;
use Kumwe\Conversion\Decimal\ExactDecimalArithmetic;
use Kumwe\Conversion\Tests\TestCase;
use Kumwe\Conversion\Value\MoneyRoundingMode;
use Kumwe\Conversion\Value\QuantityRoundingMode;

final class DecimalConformanceTest extends TestCase
{
    public function testEveryPublishedDecimalVectorHasTheExactAnswerOrRefusal(): void
    {
        $path = dirname(__DIR__, 2) . '/resources/conformance/decimal-v1.tsv';
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertTrue(is_array($lines), 'The package owns its reusable conformance corpus.');
        $this->assertSame(
            'id' . "\toperation\tleft_hex\tright_hex\tprecision\tscale\trounding\toutcome\texpected_hex",
            array_shift($lines),
            'The decimal corpus framing is pinned.'
        );
        $seen = [];
        foreach ($lines as $line) {
            $columns = explode("\t", $line);
            $this->assertSame(9, count($columns), 'Every vector has all nine columns.');
            [$id, $operation, $leftHex, $rightHex, $precision, $scale, $rounding, $outcome, $expectedHex] = $columns;
            $this->assertTrue(!isset($seen[$id]), 'Vector identifiers are unique.');
            $seen[$id] = true;
            $left = hex2bin($leftHex);
            $right = $rightHex === '-' ? null : hex2bin($rightHex);
            $this->assertTrue(is_string($left), 'Left operand uses valid hexadecimal bytes.');
            $run = static fn (): string => match ($operation) {
                'parse' => ExactDecimal::fromString($left, (int) $precision, (int) $scale)->value(),
                'multiply' => ExactDecimalArithmetic::multiply(
                    ExactDecimalArithmetic::fromLiteral($left),
                    ExactDecimalArithmetic::fromLiteral($right),
                )->value(),
                'compare' => (string) (ExactDecimalArithmetic::fromLiteral($left)->compare(
                    ExactDecimalArithmetic::fromLiteral($right),
                ) <=> 0),
                'round' => ExactDecimalArithmetic::round(
                    ExactDecimalArithmetic::fromLiteral($left),
                    (int) $precision,
                    (int) $scale,
                    MoneyRoundingMode::from($rounding)
                )->value(),
            };
            if ($outcome === 'invalid_argument') {
                $this->assertThrows($run, InvalidArgumentException::class, $id . ' must refuse.');
                continue;
            }
            $this->assertSame('value', $outcome, 'Unknown outcomes cannot silently skip a vector.');
            $expected = hex2bin($expectedHex);
            $this->assertSame($expected, $run(), $id . ' must produce exact bytes.');
            if ($operation === 'round') {
                $this->assertSame(
                    $expected,
                    ExactDecimalArithmetic::round(
                        ExactDecimalArithmetic::fromLiteral($left),
                        (int) $precision,
                        (int) $scale,
                        QuantityRoundingMode::from($rounding)
                    )->value(),
                    $id . ' quantity and money share the same rounding contract.'
                );
            }
        }
        $this->assertSame(108, count($seen), 'The complete reviewed corpus must run.');
    }
}
