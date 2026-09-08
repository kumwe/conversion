<?php

/** Generate or verify the complete source-owned public API reference. @since 0.1.4 */

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);
$manifest = json_decode((string) file_get_contents($root . '/resources/public-api/v1.json'), true, 64, JSON_THROW_ON_ERROR);
$output = "# Conversion public API\n\n";
$output .= "Every stable type and declared member below is generated from the checked API manifest and source PHPDoc.\n";
$output .= "See [architecture](architecture.md) for layering, host-owned catalogs, lifetimes and side effects.\n";
$output .= "The canonical decimal corpus defines exact cross-language output/refusal behavior; rates and rounding are inputs.\n\n";

/** Strip PHPDoc decoration while retaining all behavior, parameters and refusal details. @since 0.1.4 */
function publicApiDoc(string|false $comment): string
{
    if ($comment === false) {
        return '';
    }
    $comment = preg_replace('/^\s*\/\*\*|\*\/\s*$/', '', $comment);
    $comment = preg_replace('/^\s*\* ?/m', '', (string) $comment);
    return trim((string) $comment);
}

foreach ($manifest['types'] as $name => $type) {
    $reflection = new ReflectionClass($name);
    $output .= '## ' . $name . "\n\n" . publicApiDoc($reflection->getDocComment()) . "\n\n";
    $output .= 'Kind: `' . $type['kind'] . '`; source: `src/' . str_replace('\\', '/', substr($name, 17)) . ".php`.\n\n";
    foreach (['constants' => 'Constants', 'properties' => 'Properties'] as $key => $label) {
        if ($type[$key] !== []) {
            $output .= '### ' . $label . "\n\n```json\n";
            $output .= json_encode($type[$key], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $output .= "\n```\n\n";
        }
    }
    foreach ($type['methods'] as $method => $shape) {
        $output .= '### ' . $method . "\n\n";
        $output .= "```text\n" . publicApiDoc($reflection->getMethod($method)->getDocComment()) . "\n```\n\n";
        $output .= "```json\n" . json_encode($shape, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $output .= "\n```\n\n";
    }
}
if (in_array('--write', $argv, true)) {
    file_put_contents($root . '/docs/public-api.md', $output);
    echo "Public API reference generated.\n";
} elseif (file_get_contents($root . '/docs/public-api.md') !== $output) {
    fwrite(STDERR, "Public API reference differs from source/manifest.\n");
    exit(1);
} else {
    echo "Public API reference matches all exported source members.\n";
}
