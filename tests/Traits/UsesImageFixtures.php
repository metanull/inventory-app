<?php

namespace Tests\Traits;

/**
 * Provides access to test image fixtures stored in tests/Fixtures/images/
 *
 * This trait replaces the need for base64-encoded minimal JPEG data in tests,
 * providing real, reliable test images of various sizes.
 */
trait UsesImageFixtures
{
    /**
     * Get the path to a test image fixture
     */
    protected function getImageFixturePath(string $name = 'test-image-medium'): string
    {
        $path = base_path("tests/Fixtures/images/{$name}.jpg");

        if (! file_exists($path)) {
            throw new \RuntimeException("Image fixture not found: {$path}. Run scripts/download-test-fixtures.ps1 to download test images.");
        }

        return $path;
    }

    /**
     * Get the binary content of a test image fixture
     */
    protected function getImageFixtureContent(string $name = 'test-image-medium'): string
    {
        return file_get_contents($this->getImageFixturePath($name));
    }

    /**
     * Get a small test image (100x100, ~3KB)
     */
    protected function getSmallImageContent(): string
    {
        return $this->getImageFixtureContent('test-image-small');
    }

    /**
     * Get a medium test image (640x480, ~15KB)
     */
    protected function getMediumImageContent(): string
    {
        return $this->getImageFixtureContent('test-image-medium');
    }

    /**
     * Get a large test image (1024x768, ~150KB)
     */
    protected function getLargeImageContent(): string
    {
        return $this->getImageFixtureContent('test-image-large');
    }

    /**
     * Get a portrait test image (480x640, ~32KB)
     */
    protected function getPortraitImageContent(): string
    {
        return $this->getImageFixtureContent('test-image-portrait');
    }
}
