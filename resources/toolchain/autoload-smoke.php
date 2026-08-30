<?php

/**
 * Prove the installed production Composer autoloader exposes the complete public API.
 *
 * The script is itself a shipped package resource, so the same proof runs in the source checkout and
 * again after the exported consumer archive has been extracted. Its type list comes only from the
 * canonical package-owned public API manifest.
 *
 * @since 0.1.2
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$autoload = $root . '/vendor/autoload.php';
$manifestPath = $root . '/resources/public-api/v1.json';

if (!is_file($autoload)) {
    fwrite(STDERR, "Composer autoload smoke failed: vendor/autoload.php is missing.\n");
    exit(1);
}

require $autoload;

$contents = file_get_contents($manifestPath);
if (!is_string($contents)) {
    fwrite(STDERR, "Composer autoload smoke failed: the public API manifest is unreadable.\n");
    exit(1);
}

try {
    /** @var array{types?: array<string, array{kind?: mixed}>} $manifest */
    $manifest = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    fwrite(STDERR, 'Composer autoload smoke failed: ' . $error->getMessage() . "\n");
    exit(1);
}

$types = $manifest['types'] ?? [];
if ($types === []) {
    fwrite(STDERR, "Composer autoload smoke failed: the public API manifest contains no types.\n");
    exit(1);
}

$failures = [];
foreach ($types as $name => $shape) {
    $kind = $shape['kind'] ?? null;
    $loaded = match ($kind) {
        'class' => class_exists($name),
        'interface' => interface_exists($name),
        'enum' => enum_exists($name),
        default => false,
    };
    if (!$loaded) {
        $failures[] = sprintf('%s (%s)', $name, is_string($kind) ? $kind : 'invalid kind');
    }
}

if ($failures !== []) {
    fwrite(STDERR, "Composer autoload smoke failed:\n - " . implode("\n - ", $failures) . "\n");
    exit(1);
}

echo 'Composer autoload smoke passed: ' . count($types) . " public types loaded.\n";
