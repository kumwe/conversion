<?php

/** Compare a Composer ZIP with the complete Git-exported source tree, including every file's bytes. */

declare(strict_types=1);

if ($argc < 2 || $argc > 3) {
    throw new InvalidArgumentException('Usage: php tools/verify-archive.php ARCHIVE.zip [SOURCE_TREE=HEAD]');
}
$root = dirname(__DIR__);
$tree = $argv[2] ?? 'HEAD';
$expectedArchive = tempnam(sys_get_temp_dir(), 'kumwe-export-');
if ($expectedArchive === false) {
    throw new RuntimeException('Cannot allocate the source export.');
}

/** @return array<string, string> Exact regular-file paths and SHA-256 hashes; ZIP directory entries are immaterial. */
function archiveFileDigests(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('Cannot read archive: ' . $path);
    }
    try {
        $files = [];
        $seen = [];
        for ($index = 0; $index < $zip->numFiles; ++$index) {
            $name = $zip->getNameIndex($index);
            if (
                !is_string($name) || $name === '' || str_contains($name, "\0") || str_contains($name, '\\')
                || preg_match('~^(?:/|[A-Za-z]:)|(?:^|/)\.\.(?:/|$)|(?:^|/)\.(?:/|$)~D', $name) === 1
                || isset($seen[$name])
            ) {
                throw new RuntimeException('Unsafe or duplicate archive entry.');
            }
            $seen[$name] = true;
            if (preg_match('~^(?:tests|tools|vendor|\.github|\.git)(?:/|$)~D', $name) === 1) {
                throw new RuntimeException('Archive contains development state: ' . $name);
            }
            $system = 0;
            $attributes = 0;
            if ($zip->getExternalAttributesIndex($index, $system, $attributes)) {
                $type = ($attributes >> 16) & 0170000;
                if ($type !== 0 && $type !== 0100000 && $type !== 0040000) {
                    throw new RuntimeException('Archive contains a non-regular entry: ' . $name);
                }
            }
            if (str_ends_with($name, '/')) {
                continue;
            }
            $bytes = $zip->getFromIndex($index);
            if (!is_string($bytes)) {
                throw new RuntimeException('Cannot read archive entry: ' . $name);
            }
            $files[$name] = hash('sha256', $bytes);
        }
        ksort($files, SORT_STRING);
        return $files;
    } finally {
        $zip->close();
    }
}

try {
    $process = proc_open(
        ['git', '-C', $root, 'archive', '--format=zip', '--output=' . $expectedArchive, $tree],
        [STDIN, STDOUT, STDERR],
        $pipes,
    );
    if (!is_resource($process) || proc_close($process) !== 0) {
        throw new RuntimeException('Cannot export the reviewed Git source tree.');
    }
    $expected = archiveFileDigests($expectedArchive);
    $actual = archiveFileDigests($argv[1]);
    foreach (
        [
        'composer.json', 'resources/public-api/v1.json', 'resources/public-api/legacy-v1.json',
        'resources/capabilities/v1.json', 'resources/service-map/v1.json', 'docs/release-record.md',
        'docs/public-api.md', 'docs/integration.md', 'examples/direct-construction.php',
        ] as $required
    ) {
        if (!isset($expected[$required])) {
            throw new RuntimeException('Reviewed source export omits required consumer artifact: ' . $required);
        }
    }
    if ($actual !== $expected) {
        $missing = array_keys(array_diff_key($expected, $actual));
        $extra = array_keys(array_diff_key($actual, $expected));
        $changed = array_keys(array_diff_assoc(array_intersect_key($actual, $expected), $expected));
        throw new RuntimeException('Archive differs from reviewed source: ' . json_encode([
            'missing' => $missing, 'extra' => $extra, 'changed_bytes' => $changed,
        ], JSON_THROW_ON_ERROR));
    }
    echo count($actual) . " exported package files verified against Git source paths and SHA-256 bytes.\n";
} finally {
    unlink($expectedArchive);
}
