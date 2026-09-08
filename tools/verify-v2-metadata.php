<?php

/** Verify governed metadata and the v2 handoff without changing the stable runtime/profile API. @since 0.1.4 */

declare(strict_types=1);

$root = dirname(__DIR__);
$read = static fn (string $path): array => json_decode(
    (string) file_get_contents($root . '/' . $path),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$require = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException('V2 metadata: ' . $message);
    }
};
$legacyPath = 'resources/public-api/legacy-v1.json';
$legacy = $read($legacyPath);
$composer = $read('composer.json');
$require($composer['require'] === ['php' => '^8.5'], 'the runtime must remain PHP-only');
preg_match('/^## \[?([0-9]+\.[0-9]+\.[0-9]+)\]?/m', (string) file_get_contents($root . '/CHANGELOG.md'), $record);
$release = $record[1] ?? '';
$require($release !== '', 'one stable release record is required');
$require($legacy['package'] === $composer['name'], 'legacy package identity');
$require(count($legacy['types']) === 23, 'all 23 runtime exports must remain declared');
$symbols = [];
$extensionPoints = [];
foreach ($legacy['types'] as $name => $type) {
    $methods = [];
    foreach ($type['methods'] as $method => $shape) {
        $methods[$method] = [
            'visibility' => $shape['visibility'],
            'static' => $shape['static'],
            'parameters' => $shape['parameters'],
            'return' => $shape['return_type'],
        ];
    }
    $properties = [];
    foreach ($type['properties'] as $property => $shape) {
        $properties[$property] = [
            'type' => $shape['type'], 'static' => $shape['static'], 'readonly' => $shape['readonly'],
        ];
    }
    $constants = [];
    foreach ($type['constants'] as $constant => $shape) {
        $constants[$constant] = ['type' => $shape['type']];
    }
    foreach ($type['enum']['cases'] ?? [] as $case) {
        $constants[$case['name']] = ['type' => $name];
    }
    ksort($constants);
    $source = 'src/' . str_replace('\\', '/', substr($name, strlen($legacy['namespace']))) . '.php';
    $require(is_file($root . '/' . $source), 'exported source is missing: ' . $name);
    $symbols[$name] = [
        'kind' => $type['kind'], 'stability' => 'stable', 'file' => $source,
        'abstract' => $type['abstract'], 'final' => $type['final'], 'readonly' => $type['readonly'],
        'parent' => $type['parent'], 'interfaces' => $type['interfaces'],
        'constants' => (object) $constants, 'properties' => (object) $properties,
        'methods' => (object) $methods, 'deprecated' => null,
    ];
    if ($type['kind'] === 'interface' || $type['abstract']) {
        $extensionPoints[] = $name;
    }
}
$api = [
    'schema' => 'kumwe-package-public-api/v1', 'package' => $composer['name'], 'release' => $release,
    'namespace' => $legacy['namespace'], 'symbols' => (object) $symbols,
    'extension_points' => $extensionPoints, 'digest_of' => 'src',
];
$bytes = json_encode($api, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
if (in_array('--write', $argv, true)) {
    file_put_contents($root . '/resources/public-api/v1.json', $bytes);
    echo "Governed public API projection recorded; review and refresh handoff digests.\n";
    exit(0);
}
$require(file_get_contents($root . '/resources/public-api/v1.json') === $bytes, 'governed API projection drift');
$capabilities = $read('resources/capabilities/v1.json');
$services = $read('resources/service-map/v1.json');
foreach (['capabilities' => $capabilities, 'service-map' => $services] as $kind => $manifest) {
    $require($manifest['schema'] === 'kumwe-package-' . $kind . '/v1', $kind . ' schema identity');
    $require($manifest['package'] === $composer['name'], $kind . ' package identity');
    $require($manifest['release'] === $release, $kind . ' release identity');
}
$require($capabilities['namespace'] === $legacy['namespace'], 'capability namespace');
$covered = [];
$ids = [];
foreach ($capabilities['capabilities'] as $capability) {
    $require(!isset($ids[$capability['id']]), 'duplicate capability identity');
    $ids[$capability['id']] = true;
    foreach ($capability['symbols'] as $symbol) {
        $require(isset($symbols[$symbol]), 'capability names a foreign symbol');
        $covered[$symbol] = true;
    }
    foreach ($capability['documentation'] as $path) {
        $require(is_file($root . '/' . $path), 'capability documentation missing');
    }
}
$require(array_diff_key($symbols, $covered) === [], 'every public symbol needs capability ownership');
$require($services['config_provider'] === null, 'no ambient catalog/provider is permitted');
$require($services['factories'] === [] && $services['aliases'] === [], 'direct construction has no service defaults');
$require($services['delegators'] === [] && $services['configuration_keys'] === [], 'no hidden host configuration');
$require(
    is_string($services['provider_absence_reason']) && $services['provider_absence_reason'] !== '',
    'provider rationale',
);
$handoffBytes = (string) file_get_contents($root . '/MIGRATION-HANDOFF.md');
$parts = explode("\n---\n", $handoffBytes, 2);
$require(str_starts_with($parts[0], "---\n") && count($parts) === 2, 'v2 handoff front matter');
// The handoff deliberately uses JSON-compatible YAML 1.2 for dependency-free verification.
$handoff = json_decode(substr($parts[0], 4), true, 512, JSON_THROW_ON_ERROR);
$require($handoff['schema'] === 'kumwe-migration-handoff/v2', 'handoff schema identity');
$require($handoff['framework_php']['composer_package'] === $composer['name'], 'handoff package identity');
$require($handoff['framework_php']['public_api_manifest'] === 'resources/public-api/v1.json', 'canonical API location');
$observed = [];
foreach ($handoff['ownership']['public_manifests'] as $manifest) {
    $path = $manifest['path'];
    $require(is_file($root . '/' . $path), 'handoff file is missing: ' . $path);
    $require(hash_file('sha256', $root . '/' . $path) === $manifest['sha256'], 'handoff hash drift: ' . $path);
    $require(!isset($observed[$path]), 'duplicate handoff file: ' . $path);
    $observed[$path] = true;
}
foreach (
    [
    'resources/public-api/v1.json', $legacyPath, 'resources/capabilities/v1.json',
    'resources/service-map/v1.json', 'resources/conformance/decimal-v1.tsv',
    ] as $path
) {
    $require(isset($observed[$path]), 'handoff must identify ' . $path);
}
$require(
    hash_file('sha256', $root . '/' . $legacyPath)
        === 'aa8302264a28005c0ff67148a2c9e61c11c956be85f3fa020d6f21688e98ba0d',
    'the published 0.1.3 runtime/profile manifest must remain byte-identical at its compatibility path',
);
$require(
    hash_file('sha256', $root . '/resources/conformance/decimal-v1.tsv')
        === '635db251898707828e24f12b1abb672273552f5f633186a725cc9f50ac08140c',
    'the established decimal conformance corpus must remain byte-identical',
);
foreach (
    [
    'CHARTER.md', 'README.md', 'docs/public-api.md', 'docs/architecture.md', 'docs/integration.md',
    'docs/app-agreement.md', 'docs/decimal-conformance.md', 'docs/test-ownership.md',
    'examples/direct-construction.php',
    ] as $path
) {
    $require(is_file($root . '/' . $path) && filesize($root . '/' . $path) > 0, 'required shipped document: ' . $path);
}
echo "Governed manifests, 23 API exports, compatibility profile, corpus and v2 handoff hashes verified.\n";
