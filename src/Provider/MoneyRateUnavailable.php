<?php

declare(strict_types=1);

namespace Kumwe\Conversion\Provider;

use RuntimeException;

/**
 * Refusal raised when no contributed provider can supply a rate for a conversion.
 *
 * An installation with no rate provider is the ordinary state of core, so this is a stated outcome
 * rather than a defect: the caller presents the stored amount instead of an unevidenced converted one.
 *
 * @since  0.1.0
 */
final class MoneyRateUnavailable extends RuntimeException
{
}
