<?php

/**
 * Verify the canonical public PHP surface against its reviewed generation pin.
 *
 * The pin is generated entirely from reflection over this package's PSR-4 source
 * tree. It contains no commit, timestamp, platform path, or other environmental
 * value, so the same source produces byte-identical JSON on every supported PHP
 * runtime. Run with --write only when a reviewed compatibility change deliberately
 * accepts a new surface for this generation.
 *
 * @since 0.1.2
 */

declare(strict_types=1);

const CONVERSION_API_ROOT = __DIR__ . '/..';
const CONVERSION_API_SOURCE = CONVERSION_API_ROOT . '/src';
const CONVERSION_API_PREFIX = 'Kumwe\\Conversion\\';
const CONVERSION_API_MANIFEST = CONVERSION_API_ROOT . '/resources/public-api/v1.json';
const CONVERSION_API_EXTENSION_PROVIDER_TYPES = [
    'Kumwe\\Conversion\\Contract\\MoneyConversionRequest',
    'Kumwe\\Conversion\\Contract\\UnitConversionRequest',
    'Kumwe\\Conversion\\Decimal\\ExactDecimal',
    'Kumwe\\Conversion\\Decimal\\ExactDecimalArithmetic',
    'Kumwe\\Conversion\\Decimal\\ExactRoundingRule',
    'Kumwe\\Conversion\\Provider\\MoneyRateProvider',
    'Kumwe\\Conversion\\Provider\\MoneyRateUnavailable',
    'Kumwe\\Conversion\\Provider\\UnitConversionProvider',
    'Kumwe\\Conversion\\Provider\\UnitConversionUnavailable',
    'Kumwe\\Conversion\\Value\\MoneyExchangeRate',
    'Kumwe\\Conversion\\Value\\MoneyRoundingMode',
    'Kumwe\\Conversion\\Value\\MoneyValue',
    'Kumwe\\Conversion\\Value\\QuantityRoundingMode',
    'Kumwe\\Conversion\\Value\\QuantityValue',
    'Kumwe\\Conversion\\Value\\UnitConversionFactor',
];

try {
    exit(conversionApiMain($argv));
} catch (Throwable $error) {
    fwrite(STDERR, "Public API verification failed: {$error->getMessage()}\n");
    exit(1);
}

/**
 * Generate the current surface and either verify or deliberately write its pin.
 *
 * @param   list<string>  $arguments  Command name followed by no option or --write.
 *
 * @return  int  Process status.
 *
 * @since   0.1.2
 */
function conversionApiMain(array $arguments): int
{
    $options = array_slice($arguments, 1);
    if ($options !== [] && $options !== ['--write']) {
        fwrite(STDERR, "Usage: php tools/verify-public-api.php [--write]\n");

        return 2;
    }

    conversionApiRegisterAutoloader();
    $manifest = conversionApiManifest();
    $bytes = json_encode(
        $manifest,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ) . "\n";

    if ($options === ['--write']) {
        conversionApiWriteManifest($bytes);
        fwrite(
            STDOUT,
            sprintf(
                "Public API generation 1 accepted: %d canonical types written to %s.\n",
                count($manifest['types']),
                conversionApiRelativePath(CONVERSION_API_MANIFEST),
            ),
        );

        return 0;
    }

    if (!is_file(CONVERSION_API_MANIFEST)) {
        fwrite(
            STDERR,
            "Public API generation 1 has no pin. Review the generated surface, then run this tool with --write.\n",
        );

        return 1;
    }

    $expectedBytes = file_get_contents(CONVERSION_API_MANIFEST);
    if ($expectedBytes === false) {
        throw new RuntimeException('Cannot read ' . conversionApiRelativePath(CONVERSION_API_MANIFEST) . '.');
    }
    if ($expectedBytes !== $bytes) {
        conversionApiReportDifference($expectedBytes, $manifest);

        return 1;
    }

    fwrite(
        STDOUT,
        sprintf(
            "Public API generation 1 is current: %d canonical types are compatibility-pinned.\n",
            count($manifest['types']),
        ),
    );

    return 0;
}

/**
 * Register the package's dependency-free PSR-4 loader.
 *
 * @return  void
 *
 * @since   0.1.2
 */
function conversionApiRegisterAutoloader(): void
{
    spl_autoload_register(static function (string $class): void {
        if (!str_starts_with($class, CONVERSION_API_PREFIX)) {
            return;
        }

        $relative = substr($class, strlen(CONVERSION_API_PREFIX));
        $path = CONVERSION_API_SOURCE . '/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require $path;
        }
    });
}

/**
 * Render the complete canonical public surface.
 *
 * @return  array{
 *              schema: int,
 *              package: string,
 *              namespace: string,
 *              profiles: array<string, array{roots: list<string>, types: list<string>, digest: string}>,
 *              types: array<string, array<string, mixed>>
 *          }  Deterministic manifest document.
 *
 * @since   0.1.2
 */
function conversionApiManifest(): array
{
    $types = [];
    foreach (conversionApiTypeNames() as $name) {
        $types[$name] = conversionApiType(new ReflectionClass($name));
    }
    ksort($types, SORT_STRING);

    return [
        'schema' => 1,
        'package' => 'kumwe/conversion',
        'namespace' => CONVERSION_API_PREFIX,
        'profiles' => [
            'extension-provider-v1' => conversionApiExtensionProviderProfile($types),
        ],
        'types' => $types,
    ];
}

/**
 * Build the provider profile and digest only its compatibility-relevant projection.
 *
 * A non-provider public type may change without moving this digest. A change to a
 * profile root, member, or any reflected shape in its closure necessarily moves it.
 * This is the value a consumer pins instead of copying the package's class shapes.
 *
 * @param   array<string, array<string, mixed>>  $allTypes  Complete reflected package surface.
 *
 * @return  array{roots: list<string>, types: list<string>, digest: string}  Provider profile.
 *
 * @since   0.1.2
 */
function conversionApiExtensionProviderProfile(array $allTypes): array
{
    $roots = [
        'Kumwe\\Conversion\\Provider\\MoneyRateProvider',
        'Kumwe\\Conversion\\Provider\\UnitConversionProvider',
    ];
    $profileTypes = [];
    foreach (CONVERSION_API_EXTENSION_PROVIDER_TYPES as $profileType) {
        if (!array_key_exists($profileType, $allTypes)) {
            throw new RuntimeException("Extension-provider profile type {$profileType} is absent from the API.");
        }
        $profileTypes[$profileType] = $allTypes[$profileType];
    }

    $evidence = json_encode(
        [
            'schema' => 1,
            'profile' => 'extension-provider-v1',
            'roots' => $roots,
            'types' => $profileTypes,
        ],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );

    return [
        'roots' => $roots,
        'types' => CONVERSION_API_EXTENSION_PROVIDER_TYPES,
        'digest' => 'sha256:' . hash('sha256', $evidence),
    ];
}

/**
 * Discover each PSR-4 type declared by the package source tree.
 *
 * One class-like declaration per PSR-4 file is part of this package's source
 * convention. A source file that does not resolve under that convention fails
 * closed instead of silently disappearing from the compatibility pin.
 *
 * @return  list<class-string>  Sorted canonical names.
 *
 * @since   0.1.2
 */
function conversionApiTypeNames(): array
{
    if (!is_dir(CONVERSION_API_SOURCE)) {
        throw new RuntimeException('The source directory is missing.');
    }

    $paths = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(CONVERSION_API_SOURCE, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo) {
            throw new RuntimeException('Source discovery returned an invalid filesystem entry.');
        }
        if ($file->isFile() && $file->getExtension() === 'php') {
            $paths[] = $file->getPathname();
        }
    }
    sort($paths, SORT_STRING);

    $names = [];
    foreach ($paths as $path) {
        $relative = substr($path, strlen(CONVERSION_API_SOURCE) + 1, -4);
        $name = CONVERSION_API_PREFIX . str_replace('/', '\\', $relative);
        if (
            !class_exists($name)
            && !interface_exists($name)
            && !trait_exists($name)
            && !enum_exists($name)
        ) {
            throw new RuntimeException(
                sprintf('%s does not declare its expected PSR-4 type %s.', conversionApiRelativePath($path), $name),
            );
        }
        $names[] = $name;
    }

    if ($names === []) {
        throw new RuntimeException('The public API cannot be empty.');
    }

    return $names;
}

/**
 * Render one class-like declaration and only the members it declares.
 *
 * Public members are the consumer API. Protected members are included because
 * they become a subclass API whenever a future type is intentionally extensible.
 * Private implementation remains free to change.
 *
 * @param   ReflectionClass<object>  $type  Reflected canonical declaration.
 *
 * @return  array<string, mixed>  Compatibility-relevant surface.
 *
 * @since   0.1.2
 */
function conversionApiType(ReflectionClass $type): array
{
    $name = $type->getName();
    $parent = $type->getParentClass();
    $interfaces = $type->getInterfaceNames();
    sort($interfaces, SORT_STRING);
    $kind = 'class';
    if ($type->isEnum()) {
        $kind = 'enum';
    } elseif ($type->isInterface()) {
        $kind = 'interface';
    } elseif ($type->isTrait()) {
        $kind = 'trait';
    }

    $manifest = [
        'kind' => $kind,
        'abstract' => $type->isAbstract(),
        'final' => $type->isFinal(),
        'readonly' => $type->isReadOnly(),
        'parent' => $parent === false ? null : $parent->getName(),
        'interfaces' => $interfaces,
        'constants' => conversionApiConstants($type, $name),
        'properties' => conversionApiProperties($type, $name),
        'methods' => conversionApiMethods($type, $name),
    ];

    if ($type->isEnum()) {
        if (!enum_exists($name)) {
            throw new RuntimeException("Reflected enum {$name} cannot be loaded.");
        }
        $manifest['enum'] = conversionApiEnum(new ReflectionEnum($name));
    }

    return $manifest;
}

/**
 * Render declared public and protected constants.
 *
 * @param   ReflectionClass<object>  $type   Reflected declaration.
 * @param   string                   $owner  Canonical declaring name.
 *
 * @return  array<string, array<string, mixed>>  Constants keyed by name.
 *
 * @since   0.1.2
 */
function conversionApiConstants(ReflectionClass $type, string $owner): array
{
    $constants = [];
    $filter = ReflectionClassConstant::IS_PUBLIC | ReflectionClassConstant::IS_PROTECTED;
    foreach ($type->getReflectionConstants($filter) as $constant) {
        if ($constant->getDeclaringClass()->getName() !== $owner || $constant->isEnumCase()) {
            continue;
        }
        $constantType = $constant->getType();
        $constants[$constant->getName()] = [
            'visibility' => conversionApiVisibility($constant),
            'final' => $constant->isFinal(),
            'type' => $constantType === null ? null : conversionApiReflectionType($constantType, $owner),
            'value' => conversionApiValue($constant->getValue()),
        ];
    }
    ksort($constants, SORT_STRING);

    return $constants;
}

/**
 * Render declared public and protected properties.
 *
 * @param   ReflectionClass<object>  $type   Reflected declaration.
 * @param   string                   $owner  Canonical declaring name.
 *
 * @return  array<string, array<string, mixed>>  Properties keyed by name.
 *
 * @since   0.1.2
 */
function conversionApiProperties(ReflectionClass $type, string $owner): array
{
    $properties = [];
    $filter = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
    foreach ($type->getProperties($filter) as $property) {
        if ($property->getDeclaringClass()->getName() !== $owner) {
            continue;
        }
        if ($type->isEnum() && in_array($property->getName(), ['name', 'value'], true)) {
            // These engine-synthesized properties are implied by kind/backing_type
            // and have differed in reflection metadata between PHP releases.
            continue;
        }
        $propertyType = $property->getType();
        $entry = [
            'visibility' => conversionApiVisibility($property),
            'static' => $property->isStatic(),
            'readonly' => $property->isReadOnly(),
            'type' => $propertyType === null ? null : conversionApiReflectionType($propertyType, $owner),
        ];
        if ($property->hasDefaultValue()) {
            $entry['default'] = conversionApiValue($property->getDefaultValue());
        }
        $properties[$property->getName()] = $entry;
    }
    ksort($properties, SORT_STRING);

    return $properties;
}

/**
 * Render declared public and protected methods.
 *
 * @param   ReflectionClass<object>  $type   Reflected declaration.
 * @param   string                   $owner  Canonical declaring name.
 *
 * @return  array<string, array<string, mixed>>  Methods keyed by name.
 *
 * @since   0.1.2
 */
function conversionApiMethods(ReflectionClass $type, string $owner): array
{
    $methods = [];
    $filter = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED;
    foreach ($type->getMethods($filter) as $method) {
        if ($method->getDeclaringClass()->getName() !== $owner) {
            continue;
        }
        if ($type->isEnum() && in_array($method->getName(), ['cases', 'from', 'tryFrom'], true)) {
            // These engine-synthesized methods are implied by kind/backing_type;
            // only methods declared in package source belong in the source pin.
            continue;
        }
        $returnType = $method->getReturnType();
        $methods[$method->getName()] = [
            'visibility' => conversionApiVisibility($method),
            'static' => $method->isStatic(),
            'final' => $method->isFinal(),
            'abstract' => $method->isAbstract(),
            'returns_reference' => $method->returnsReference(),
            'parameters' => array_map(
                static fn (ReflectionParameter $parameter): array => conversionApiParameter($parameter, $owner),
                $method->getParameters(),
            ),
            'return_type' => $returnType === null ? null : conversionApiReflectionType($returnType, $owner),
        ];
    }
    ksort($methods, SORT_STRING);

    return $methods;
}

/**
 * Render one ordered method parameter.
 *
 * @param   ReflectionParameter  $parameter  Reflected parameter.
 * @param   string               $owner      Canonical declaring type name.
 *
 * @return  array<string, mixed>  Compatibility-relevant parameter shape.
 *
 * @since   0.1.2
 */
function conversionApiParameter(ReflectionParameter $parameter, string $owner): array
{
    $type = $parameter->getType();
    $entry = [
        'name' => $parameter->getName(),
        'type' => $type === null ? null : conversionApiReflectionType($type, $owner),
        'by_reference' => $parameter->isPassedByReference(),
        'variadic' => $parameter->isVariadic(),
        'optional' => $parameter->isOptional(),
    ];
    if ($parameter->isDefaultValueAvailable()) {
        $entry['default'] = $parameter->isDefaultValueConstant()
            ? ['kind' => 'constant', 'name' => $parameter->getDefaultValueConstantName()]
            : ['kind' => 'value', 'value' => conversionApiValue($parameter->getDefaultValue())];
    }

    return $entry;
}

/**
 * Render an enum's backing type and cases in observable declaration order.
 *
 * @param   ReflectionEnum<UnitEnum>  $enum  Reflected enum.
 *
 * @return  array{backing_type: ?string, cases: list<array{name: string, value?: int|string}>}
 *          Enum surface.
 *
 * @since   0.1.2
 */
function conversionApiEnum(ReflectionEnum $enum): array
{
    $backingType = $enum->getBackingType();
    $cases = [];
    foreach ($enum->getCases() as $case) {
        $entry = ['name' => $case->getName()];
        if ($case instanceof ReflectionEnumBackedCase) {
            $entry['value'] = $case->getBackingValue();
        }
        $cases[] = $entry;
    }

    return [
        'backing_type' => $backingType === null ? null : conversionApiReflectionType($backingType, $enum->getName()),
        'cases' => $cases,
    ];
}

/**
 * Render a named, union, or intersection type without import abbreviations.
 *
 * @param   ReflectionType  $type   Reflected declaration type.
 * @param   string          $owner  Canonical declaring type name used to normalize relative types.
 *
 * @return  string  Canonical type expression.
 *
 * @since   0.1.2
 */
function conversionApiReflectionType(ReflectionType $type, string $owner): string
{
    if ($type instanceof ReflectionNamedType) {
        $name = $type->getName();
        if ($name === 'self') {
            $name = $owner;
        } elseif ($name === 'parent') {
            $parent = get_parent_class($owner);
            if ($parent === false) {
                throw new RuntimeException("Relative parent type on {$owner} has no parent class.");
            }
            $name = $parent;
        }

        return $type->allowsNull() && !in_array($name, ['mixed', 'null'], true) ? '?' . $name : $name;
    }
    if ($type instanceof ReflectionUnionType) {
        return implode('|', array_map(
            static fn (ReflectionType $member): string => conversionApiReflectionType($member, $owner),
            $type->getTypes(),
        ));
    }
    if ($type instanceof ReflectionIntersectionType) {
        return implode('&', array_map(
            static fn (ReflectionType $member): string => conversionApiReflectionType($member, $owner),
            $type->getTypes(),
        ));
    }

    throw new RuntimeException('Unknown reflection type kind ' . $type::class . '.');
}

/**
 * Normalize a reflection value into deterministic JSON data.
 *
 * @param   mixed  $value  Constant, property-default, or parameter-default value.
 *
 * @return  mixed  JSON-compatible value preserving PHP array order.
 *
 * @since   0.1.2
 */
function conversionApiValue(mixed $value): mixed
{
    if ($value === null || is_scalar($value)) {
        return $value;
    }
    if (is_array($value)) {
        $normalized = [];
        foreach ($value as $key => $member) {
            $normalized[$key] = conversionApiValue($member);
        }

        return $normalized;
    }

    throw new RuntimeException('Unsupported reflected default value of type ' . get_debug_type($value) . '.');
}

/**
 * Return one reflection member's declared visibility.
 *
 * @param   ReflectionClassConstant|ReflectionProperty|ReflectionMethod  $member  Reflected member.
 *
 * @return  string  public or protected.
 *
 * @since   0.1.2
 */
function conversionApiVisibility(ReflectionClassConstant|ReflectionProperty|ReflectionMethod $member): string
{
    return $member->isPublic() ? 'public' : 'protected';
}

/**
 * Atomically replace the reviewed generation pin.
 *
 * @param   string  $bytes  Canonical manifest JSON.
 *
 * @return  void
 *
 * @since   0.1.2
 */
function conversionApiWriteManifest(string $bytes): void
{
    $directory = dirname(CONVERSION_API_MANIFEST);
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new RuntimeException('Cannot create ' . conversionApiRelativePath($directory) . '.');
    }

    $temporary = tempnam($directory, '.public-api-');
    if ($temporary === false) {
        throw new RuntimeException('Cannot create a temporary API manifest.');
    }

    try {
        if (file_put_contents($temporary, $bytes, LOCK_EX) !== strlen($bytes)) {
            throw new RuntimeException('Cannot write the complete API manifest.');
        }
        if (!rename($temporary, CONVERSION_API_MANIFEST)) {
            throw new RuntimeException('Cannot replace the API manifest atomically.');
        }
    } finally {
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
}

/**
 * Report the first semantic difference or non-canonical JSON formatting.
 *
 * @param   string                $expectedBytes  Checked-in pin bytes.
 * @param   array<string, mixed>  $actual         Generated surface.
 *
 * @return  void
 *
 * @since   0.1.2
 */
function conversionApiReportDifference(string $expectedBytes, array $actual): void
{
    try {
        $expected = json_decode($expectedBytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        fwrite(STDERR, "The public API pin is not valid JSON: {$error->getMessage()}\n");

        return;
    }

    $difference = conversionApiFirstDifference($expected, $actual);
    if ($difference === null) {
        fwrite(
            STDERR,
            "The public API pin is semantically current but is not canonical JSON; run --write to normalize it.\n",
        );

        return;
    }

    fwrite(
        STDERR,
        sprintf(
            "Public API generation 1 drifted at %s.\nExpected: %s\nActual:   %s\n"
                . "Treat incompatible changes as a new package major; use --write only after compatibility review.\n",
            $difference['path'],
            conversionApiDisplayValue($difference['expected']),
            conversionApiDisplayValue($difference['actual']),
        ),
    );
}

/**
 * Find the first semantic difference between two decoded manifest values.
 *
 * @param   mixed   $expected  Pinned value.
 * @param   mixed   $actual    Generated value.
 * @param   string  $path      JSONPath-like location.
 *
 * @return  ?array{path: string, expected: mixed, actual: mixed}  First difference.
 *
 * @since   0.1.2
 */
function conversionApiFirstDifference(mixed $expected, mixed $actual, string $path = '$'): ?array
{
    if (!is_array($expected)) {
        if (is_array($actual)) {
            return ['path' => $path, 'expected' => $expected, 'actual' => $actual];
        }

        return $expected === $actual ? null : ['path' => $path, 'expected' => $expected, 'actual' => $actual];
    }
    if (!is_array($actual)) {
        return ['path' => $path, 'expected' => $expected, 'actual' => $actual];
    }

    $expectedKeys = array_keys($expected);
    $actualKeys = array_keys($actual);
    $allKeys = array_values(array_unique([...$expectedKeys, ...$actualKeys], SORT_REGULAR));
    foreach ($allKeys as $key) {
        $memberPath = is_int($key) ? $path . '[' . $key . ']' : $path . '.' . $key;
        if (!array_key_exists($key, $expected)) {
            return ['path' => $memberPath, 'expected' => '<absent>', 'actual' => $actual[$key]];
        }
        if (!array_key_exists($key, $actual)) {
            return ['path' => $memberPath, 'expected' => $expected[$key], 'actual' => '<absent>'];
        }
        $difference = conversionApiFirstDifference($expected[$key], $actual[$key], $memberPath);
        if ($difference !== null) {
            return $difference;
        }
    }

    return null;
}

/**
 * Render a concise JSON-compatible difference value.
 *
 * @param   mixed  $value  Difference member.
 *
 * @return  string  Single-line diagnostic representation.
 *
 * @since   0.1.2
 */
function conversionApiDisplayValue(mixed $value): string
{
    $encoded = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return $encoded === false ? var_export($value, true) : $encoded;
}

/**
 * Spell a repository path without environment-specific prefixes.
 *
 * @param   string  $path  Absolute repository path.
 *
 * @return  string  Slash-normalized relative path.
 *
 * @since   0.1.2
 */
function conversionApiRelativePath(string $path): string
{
    return str_replace('\\', '/', substr($path, strlen(CONVERSION_API_ROOT) + 1));
}
