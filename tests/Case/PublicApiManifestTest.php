<?php

/**
 * Holds the package-owned public API generation to its canonical manifest.
 *
 * @since 0.1.2
 */

declare(strict_types=1);

namespace Kumwe\Conversion\Tests\Case;

use Kumwe\Conversion\Tests\TestCase;

/**
 * Proves the pin is canonical, complete, and independent of historical names.
 *
 * @since  0.1.2
 */
final class PublicApiManifestTest extends TestCase
{
    /**
     * The checked-in pin contains exactly the package's 23 canonical public types.
     *
     * @return  void
     *
     * @since   0.1.2
     */
    public function testManifestPinsOnlyTheCanonicalSurface(): void
    {
        $path = dirname(__DIR__, 2) . '/resources/public-api/v1.json';
        $bytes = file_get_contents($path);
        $this->assertTrue(is_string($bytes), 'The public API generation pin must be readable.');

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
        $types = $manifest['types'] ?? null;
        $this->assertSame(1, $manifest['schema'] ?? null, 'The manifest uses schema generation 1.');
        $this->assertSame('kumwe/conversion', $manifest['package'] ?? null, 'The package coordinate is pinned.');
        $this->assertSame(
            'Kumwe\\Conversion\\',
            $manifest['namespace'] ?? null,
            'The canonical namespace is pinned.',
        );
        $profiles = $manifest['profiles'] ?? null;
        $this->assertTrue(is_array($profiles), 'The manifest must carry its consumer profiles.');
        $providerProfile = is_array($profiles) ? ($profiles['extension-provider-v1'] ?? null) : null;
        $this->assertTrue(is_array($providerProfile), 'The extension-provider profile must be pinned.');
        $providerTypes = is_array($providerProfile) ? ($providerProfile['types'] ?? null) : null;
        $this->assertTrue(is_array($providerTypes), 'The extension-provider profile must carry its type closure.');
        $this->assertSame(
            15,
            is_array($providerTypes) ? count($providerTypes) : 0,
            'The provider closure is complete.',
        );
        $providerDigest = is_array($providerProfile) ? ($providerProfile['digest'] ?? null) : null;
        $this->assertTrue(
            is_string($providerDigest) && preg_match('/^sha256:[0-9a-f]{64}$/D', $providerDigest) === 1,
            'The extension-provider profile must carry a canonical SHA-256 digest.',
        );
        $this->assertTrue(is_array($types), 'The manifest must carry a type map.');
        $this->assertSame(23, count($types), 'All 23 package types must be pinned.');
        foreach (array_keys($types) as $type) {
            $this->assertTrue(is_string($type), 'Every public API key must be a canonical type name.');
            $this->assertTrue(
                str_starts_with($type, 'Kumwe\\Conversion\\'),
                sprintf('The manifest must not expose a foreign or historical type: %s.', $type),
            );
        }
        foreach (is_array($providerTypes) ? $providerTypes : [] as $providerType) {
            $this->assertTrue(is_string($providerType), 'Every provider-profile member must be a type name.');
            $this->assertTrue(
                is_array($types) && array_key_exists($providerType, $types),
                sprintf('The provider-profile member must exist in the package API: %s.', $providerType),
            );
        }
        $this->assertStringExcludes(
            'Kumwe\\App\\',
            $bytes,
            'The clean extraction must not retain an App namespace alias or signature.',
        );
    }

    /**
     * The verifier reproduces the checked-in generation byte for byte.
     *
     * @return  void
     *
     * @since   0.1.2
     */
    public function testGeneratedSurfaceMatchesTheReviewedPin(): void
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' '
            . escapeshellarg(dirname(__DIR__, 2) . '/tools/verify-public-api.php')
            . ' 2>&1';
        $output = [];
        $status = 1;
        exec($command, $output, $status);

        $this->assertSame(
            0,
            $status,
            "The public API verifier must accept the reviewed pin.\n" . implode("\n", $output),
        );
        $this->assertStringContains(
            '23 canonical types are compatibility-pinned',
            implode("\n", $output),
            'The verifier must prove the full non-empty surface.',
        );
    }
}
