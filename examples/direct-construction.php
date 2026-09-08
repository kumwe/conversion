<?php

/** Standalone exact-value and converter construction. @since 0.1.4 */

declare(strict_types=1);

use Kumwe\Conversion\Contract\MoneyConverter;
use Kumwe\Conversion\Contract\QuantityConverter;
use Kumwe\Conversion\Decimal\ExactDecimal;

require $argv[1] ?? dirname(__DIR__) . '/vendor/autoload.php';

$decimal = ExactDecimal::fromString('12.5', 8, 3);
if ($decimal->value() !== '12.500') {
    throw new RuntimeException('Canonical decimal construction failed.');
}
$money = new MoneyConverter();
$quantity = new QuantityConverter();
if (!$money instanceof MoneyConverter || !$quantity instanceof QuantityConverter) {
    throw new RuntimeException('Pure conversion construction failed.');
}
echo "Canonical decimal and direct conversion rules constructed without a container.\n";
