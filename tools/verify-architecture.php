<?php

/** Refuse host/storage dependencies and reversed exact-decimal layering. @since 0.1.3 */

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$allowed = ['Decimal' => ['Decimal'], 'Value' => ['Decimal', 'Value'],
    'Contract' => ['Decimal', 'Value', 'Contract'], 'Provider' => ['Decimal', 'Value', 'Contract', 'Provider']];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/src', FilesystemIterator::SKIP_DOTS),
);
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $relative = substr($file->getPathname(), strlen($root . '/src/'));
    $owner = explode('/', $relative)[0];
    if (!isset($allowed[$owner])) {
        $errors[] = 'Unclassified source layer: ' . $relative;
        continue;
    }
    $code = file_get_contents($file->getPathname());
    foreach (token_get_all($code === false ? '' : $code) as $token) {
        if (!is_array($token)) {
            continue;
        }
        if (in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
            $name = ltrim($token[1], '\\');
            if (str_starts_with($name, 'Kumwe\\Conversion\\')) {
                $target = explode('\\', $name)[2];
                if (!in_array($target, $allowed[$owner], true)) {
                    $errors[] = $relative . ' reverses dependency into ' . $target;
                }
            } elseif (str_contains($name, '\\')) {
                $errors[] = $relative . ' imports a foreign dependency: ' . $name;
            }
        }
        if (
            $token[0] === T_STRING && in_array(strtolower($token[1]), [
            'pdo', 'mysqli', 'curl_exec', 'file_get_contents', 'file_put_contents', 'fopen', 'exec',
            'shell_exec', 'proc_open', 'getenv', 'class_alias',
            ], true)
        ) {
            $errors[] = $relative . ' introduces host I/O or alias authority: ' . $token[1];
        }
    }
}
$composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 32, JSON_THROW_ON_ERROR);
if (array_keys($composer['require']) !== ['php']) {
    $errors[] = 'Conversion must remain a PHP-only runtime contract.';
}
if ($errors !== []) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}
echo "Conversion architecture boundary passed.\n";
