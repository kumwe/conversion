<?php

/** Exercise acceptance and refusal of the archive integrity gate in an isolated Git fixture. */

declare(strict_types=1);

$workspace = sys_get_temp_dir() . '/kumwe-archive-fixtures-' . bin2hex(random_bytes(8));
$root = $workspace . '/source';
mkdir($root . '/tools', 0700, true);

/** @return array{int, string} Exit code and diagnostic output. */
function archiveFixtureCommand(array $arguments, string $directory): array
{
    $process = proc_open($arguments, [['file', '/dev/null', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes, $directory);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start archive fixture command.');
    }
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), $output];
}

/** Write small stored ZIP entries directly so the duplicate-name refusal can also be exercised. */
function archiveFixtureZip(string $path, array $entries): void
{
    $body = '';
    $directory = '';
    foreach ($entries as [$name, $bytes, $mode]) {
        $length = strlen($bytes);
        $checksum = crc32($bytes);
        $offset = strlen($body);
        $body .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $checksum, $length, $length, strlen($name), 0);
        $body .= $name . $bytes;
        $directory .= pack(
            'VvvvvvvVVVvvvvvVV',
            0x02014b50,
            (3 << 8) | 20,
            20,
            0,
            0,
            0,
            0,
            $checksum,
            $length,
            $length,
            strlen($name),
            0,
            0,
            0,
            0,
            $mode << 16,
            $offset,
        ) . $name;
    }
    $end = pack('VvvvvVVv', 0x06054b50, 0, 0, count($entries), count($entries), strlen($directory), strlen($body), 0);
    file_put_contents($path, $body . $directory . $end);
}

try {
    copy(__DIR__ . '/verify-archive.php', $root . '/tools/verify-archive.php');
    $names = [
        'composer.json', 'resources/public-api/v1.json', 'resources/public-api/legacy-v1.json',
        'resources/capabilities/v1.json', 'resources/service-map/v1.json', 'MIGRATION-HANDOFF.md',
        'docs/public-api.md', 'docs/integration.md', 'examples/direct-construction.php',
        'src/Sample.php', 'README.md',
    ];
    $entries = [];
    foreach ($names as $name) {
        if (!is_dir(dirname($root . '/' . $name))) {
            mkdir(dirname($root . '/' . $name), 0700, true);
        }
        file_put_contents($root . '/' . $name, $name . "\n");
        $entries[] = [$name, $name . "\n", 0100644];
    }
    file_put_contents($root . '/.gitattributes', "/tools export-ignore\n/.gitattributes export-ignore\n");
    foreach (
        [
        ['git', 'init', '-q'],
        ['git', 'add', '.'],
        ['git', '-c', 'user.name=Archive Fixture', '-c', 'user.email=fixture@example.invalid',
            'commit', '-qm', 'fixture'],
        ] as $arguments
    ) {
        [$status, $output] = archiveFixtureCommand($arguments, $root);
        if ($status !== 0) {
            throw new RuntimeException('Cannot prepare isolated Git fixture: ' . $output);
        }
    }
    $withoutReadme = array_values(array_filter($entries, static fn (array $entry): bool => $entry[0] !== 'README.md'));
    $changed = array_map(
        static fn (array $entry): array => $entry[0] === 'src/Sample.php'
            ? [$entry[0], $entry[1] . 'changed', $entry[2]] : $entry,
        $entries,
    );
    $cases = [
        'exact-export' => [$entries, null],
        'missing-file' => [$withoutReadme, 'missing'],
        'changed-bytes' => [$changed, 'changed_bytes'],
        'extra-file' => [[...$entries, ['extra.txt', 'extra', 0100644]], 'extra'],
        'development-file' => [[...$entries, ['tools/leak.php', 'leak', 0100644]], 'development state'],
        'unsafe-path' => [[...$entries, ['../outside', 'escape', 0100644]], 'Unsafe'],
        'duplicate-name' => [[...$entries, ['composer.json', 'duplicate', 0100644]], 'duplicate'],
        'symbolic-link' => [[...$withoutReadme, ['README.md', "README.md\n", 0120777]], 'non-regular'],
    ];
    foreach ($cases as $name => [$content, $diagnostic]) {
        $archive = $workspace . '/' . $name . '.zip';
        archiveFixtureZip($archive, $content);
        [$status, $output] = archiveFixtureCommand([PHP_BINARY, $root . '/tools/verify-archive.php', $archive], $root);
        if (
            ($diagnostic === null && $status !== 0)
            || ($diagnostic !== null && ($status === 0 || !str_contains($output, $diagnostic)))
        ) {
            throw new RuntimeException('Archive fixture failed: ' . $name . "\n" . $output);
        }
    }
    echo "Archive integrity passed: 8 isolated acceptance/refusal fixtures.\n";
} finally {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($workspace, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($files as $file) {
        if ($file->isDir() && !$file->isLink()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    rmdir($workspace);
}
