<?php

namespace App\Support\LegacyLinks\Rules\Concerns;

use App\Models\Collection;
use App\Support\LegacyLinks\LegacyReference;

trait ResolvesThematicGalleryUrls
{
    private const THG_GALLERIES_ROOT = 'thg_galleries_root';

    private const THG_EXHIBITIONS_ROOT = 'thg_exhibitions_root';

    private const MAX_THG_CONTEXT_DEPTH = 10;

    protected function thematicGalleryLandingUrl(string $galleryId, string $legacyLanguage, ?Collection $collection = null): ?string
    {
        $baseUrl = $this->thematicGalleryBaseUrl($galleryId);

        if ($baseUrl === null) {
            return null;
        }

        $path = $this->thematicGalleryPath($galleryId, $legacyLanguage);

        if ($path === null && $collection !== null && $this->isThematicExhibitionCollection($collection)) {
            $folder = $this->thematicExhibitionFolder($collection);
            $path = $folder === null ? null : "{$folder}/{lang}";
        }

        if ($path === null) {
            return $baseUrl;
        }

        $path = trim(str_replace('{lang}', $legacyLanguage, $path), '/');

        return "{$baseUrl}/{$path}";
    }

    protected function thematicGalleryBaseUrl(string $galleryId): ?string
    {
        $baseUrl = config("legacy.links.thematic_galleries.{$galleryId}.base_url");

        return is_string($baseUrl) && $baseUrl !== '' ? rtrim($baseUrl, '/') : null;
    }

    /**
     * @return array{gallery_id: string, surface: 'gallery'|'exhibition', gallery_collection: Collection|null}|null
     */
    protected function thematicGalleryContextForCollection(Collection $collection): ?array
    {
        $galleryId = null;
        $galleryCollection = null;
        $current = $collection;

        for ($depth = 0; $depth < self::MAX_THG_CONTEXT_DEPTH; $depth++) {
            $reference = LegacyReference::parse($current->backward_compatibility);

            if ($reference?->is('mwnf3_thematic_gallery', 'thg_gallery') && $reference->hasParts(1)) {
                $galleryId ??= (string) $reference->part(0);
                $galleryCollection ??= $current;
            }

            if ($reference?->is('mwnf3_thematic_gallery', 'theme') && $reference->hasParts(1)) {
                $galleryId ??= (string) $reference->part(0);
            }

            $internalName = (string) $current->internal_name;

            if ($internalName === self::THG_GALLERIES_ROOT || $reference?->is('mwnf3_thematic_gallery', 'galleries_root')) {
                return $galleryId === null ? null : [
                    'gallery_id' => $galleryId,
                    'surface' => 'gallery',
                    'gallery_collection' => $galleryCollection,
                ];
            }

            if ($internalName === self::THG_EXHIBITIONS_ROOT || $reference?->is('mwnf3_thematic_gallery', 'exhibitions_root')) {
                return $galleryId === null ? null : [
                    'gallery_id' => $galleryId,
                    'surface' => 'exhibition',
                    'gallery_collection' => $galleryCollection,
                ];
            }

            if ($current->parent === null) {
                return null;
            }

            $current = $current->parent;
        }

        throw new \RuntimeException('Collection hierarchy depth exceeds maximum of '.self::MAX_THG_CONTEXT_DEPTH.' levels.');
    }

    protected function thematicGalleryDatabaseItemUrl(Collection $collection, LegacyReference $reference, string $legacyLanguage): ?string
    {
        if (! $reference->hasParts(4)) {
            return null;
        }

        $context = $this->thematicGalleryContextForCollection($collection);

        if ($context === null) {
            return null;
        }

        $baseUrl = $this->thematicGalleryBaseUrl($context['gallery_id']);

        if ($baseUrl === null) {
            return null;
        }

        [$project, $country, $holder, $number] = array_slice($reference->parts, 0, 4);
        $databaseItemPath = "database-item/{$reference->schema}/{$reference->table}/{$project}/{$country}/{$holder}/{$number}/{$legacyLanguage}";

        if ($context['surface'] === 'gallery') {
            return "{$baseUrl}/{$databaseItemPath}";
        }

        $configuredPath = $this->thematicGalleryPath($context['gallery_id'], $legacyLanguage);

        if ($configuredPath !== null) {
            return "{$baseUrl}/{$configuredPath}/{$databaseItemPath}";
        }

        $folder = $this->thematicExhibitionFolder($context['gallery_collection'] ?? $collection);

        return $folder === null ? null : "{$baseUrl}/{$folder}/en/{$databaseItemPath}";
    }

    protected function thematicGalleryPath(string $galleryId, string $legacyLanguage): ?string
    {
        $path = config("legacy.links.thematic_galleries.{$galleryId}.path");

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return trim(str_replace('{lang}', $legacyLanguage, $path), '/');
    }

    protected function isThematicExhibitionCollection(Collection $collection): bool
    {
        $context = $this->thematicGalleryContextForCollection($collection);

        return ($context['surface'] ?? null) === 'exhibition';
    }

    protected function thematicExhibitionFolder(Collection $collection): ?string
    {
        $internalName = trim((string) $collection->internal_name);

        if (str_starts_with($internalName, 'exhibition_')) {
            $folder = substr($internalName, strlen('exhibition_'));

            return $folder === '' ? null : $folder;
        }

        return $internalName === '' ? null : $internalName;
    }
}
