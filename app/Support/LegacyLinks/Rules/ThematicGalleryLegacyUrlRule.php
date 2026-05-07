<?php

namespace App\Support\LegacyLinks\Rules;

use App\Enums\LegacyLinkConfidence;
use App\Models\Collection;
use App\Support\LegacyLinks\LegacyLink;
use App\Support\LegacyLinks\LegacyReference;
use App\Support\LegacyLinks\Rules\Concerns\BuildsLegacyUrls;
use Illuminate\Database\Eloquent\Model;

class ThematicGalleryLegacyUrlRule implements LegacyUrlRule
{
    use BuildsLegacyUrls;

    public function supports(Model $record, LegacyReference $reference): bool
    {
        return $record instanceof Collection && $reference->schema === 'mwnf3_thematic_gallery';
    }

    public function resolve(Model $record, LegacyReference $reference, string $legacyLanguage): array
    {
        return match ($reference->table) {
            'thg_gallery' => $this->resolveGallery($reference, $legacyLanguage),
            'theme' => $this->resolveTheme($reference, $legacyLanguage),
            default => [],
        };
    }

    /** @return array<int, LegacyLink> */
    private function resolveGallery(LegacyReference $reference, string $legacyLanguage): array
    {
        if (! $reference->hasParts(1)) {
            return [];
        }

        $landing = $this->landingUrl((string) $reference->part(0), $legacyLanguage);

        if ($landing === null) {
            return [LegacyLink::diagnostic('Thematic gallery landing page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, 'The gallery id needs a configured public slug or host.')];
        }

        return [
            LegacyLink::public('Thematic gallery landing page', $landing, LegacyLinkConfidence::EXACT, $reference->raw),
            LegacyLink::backoffice('Thematic gallery back-office record', $this->backofficeUrl('thg/thg_galleries', "1;{$reference->part(0)}"), LegacyLinkConfidence::EXACT, $reference->raw),
        ];
    }

    /** @return array<int, LegacyLink> */
    private function resolveTheme(LegacyReference $reference, string $legacyLanguage): array
    {
        if (! $reference->hasParts(2)) {
            return [];
        }

        $landing = $this->landingUrl((string) $reference->part(0), $legacyLanguage);

        if ($landing === null) {
            return [LegacyLink::diagnostic('Thematic gallery theme page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, 'Theme URLs need the gallery public slug or host.')];
        }

        return [
            LegacyLink::public('Thematic gallery theme page', rtrim($landing, '/')."/theme/{$reference->part(1)}", LegacyLinkConfidence::EXACT, $reference->raw),
            LegacyLink::backoffice('Thematic gallery theme back-office record', $this->backofficeUrl('thg/thg_galleries', "1;{$reference->part(0)};{$reference->part(1)}"), LegacyLinkConfidence::EXACT, $reference->raw),
        ];
    }

    private function landingUrl(string $galleryId, string $legacyLanguage): ?string
    {
        $mapping = config("legacy.links.thematic_galleries.{$galleryId}");

        if (! is_array($mapping) || ! isset($mapping['base_url'])) {
            return null;
        }

        $baseUrl = rtrim((string) $mapping['base_url'], '/');
        $path = trim(str_replace('{lang}', $legacyLanguage, (string) ($mapping['path'] ?? '')), '/');

        return $path === '' ? $baseUrl : "{$baseUrl}/{$path}";
    }
}
