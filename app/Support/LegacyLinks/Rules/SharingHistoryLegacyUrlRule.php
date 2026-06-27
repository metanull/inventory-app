<?php

namespace App\Support\LegacyLinks\Rules;

use App\Enums\LegacyLinkConfidence;
use App\Models\Collection;
use App\Models\Item;
use App\Models\Partner;
use App\Support\LegacyLinks\LegacyLink;
use App\Support\LegacyLinks\LegacyReference;
use App\Support\LegacyLinks\Rules\Concerns\BuildsLegacyUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class SharingHistoryLegacyUrlRule implements LegacyUrlRule
{
    use BuildsLegacyUrls;

    public function supports(Model $record, LegacyReference $reference): bool
    {
        return $reference->schema === 'mwnf3_sharing_history'
            && ($record instanceof Item || $record instanceof Collection || $record instanceof Partner);
    }

    public function resolve(Model $record, LegacyReference $reference, string $legacyLanguage): array
    {
        if ($record instanceof Item) {
            return $this->resolveItem($reference, $legacyLanguage);
        }

        if ($record instanceof Collection) {
            return $this->resolveCollection($record, $reference, $legacyLanguage);
        }

        if ($record instanceof Partner) {
            return $this->resolvePartner($record, $reference);
        }

        return [];
    }

    /** @return array<int, LegacyLink> */
    private function resolveItem(LegacyReference $reference, string $legacyLanguage): array
    {
        $type = match ($reference->table) {
            'sh_objects', 'sh_object_images' => 'object',
            'sh_monuments', 'sh_monument_images' => 'monument',
            default => null,
        };

        if ($type === null || ! $reference->hasParts(3)) {
            return [];
        }

        [$project, $country, $number] = array_slice($reference->parts, 0, 3);
        $confidence = str_contains($reference->table, '_images') ? LegacyLinkConfidence::INFERRED : LegacyLinkConfidence::EXACT;
        $note = $confidence === LegacyLinkConfidence::INFERRED ? 'Image items resolve to the parent legacy page.' : null;

        return [
            LegacyLink::public(
                label: "Sharing History {$type} page",
                url: $this->host('sharing_history')."/database_item.php?id={$type};{$project};{$country};{$number};{$legacyLanguage}",
                confidence: $confidence,
                source: $reference->raw,
                note: $note,
            ),
            LegacyLink::backoffice(
                label: "Sharing History {$type} back-office record",
                url: $this->backofficeUrl($type === 'object' ? 'sh/sh_objects' : 'sh/sh_monuments', '1;'.strtoupper($project).";{$country};{$number}"),
                confidence: $confidence,
                source: $reference->raw,
                note: $note,
            ),
        ];
    }

    /** @return array<int, LegacyLink> */
    private function resolveCollection(Collection $collection, LegacyReference $reference, string $legacyLanguage): array
    {
        $host = $this->host('sharing_history');

        return match ($reference->table) {
            'sh_projects' => $reference->hasParts(1)
                ? [
                    LegacyLink::public('Sharing History landing page', "{$host}/index.php", LegacyLinkConfidence::EXACT, $reference->raw),
                    LegacyLink::backoffice('Sharing History project back-office record', $this->backofficeUrl('sh/sh_projects', '1;'.strtoupper((string) $reference->part(0))), LegacyLinkConfidence::EXACT, $reference->raw),
                ]
                : [],
            'sh_exhibitions' => $reference->hasParts(1)
                ? [
                    LegacyLink::public('Sharing History exhibition items', "{$host}/exh_items.php?eId={$reference->part(0)}&lan={$legacyLanguage}", LegacyLinkConfidence::INFERRED, $reference->raw),
                    LegacyLink::backoffice('Sharing History exhibition back-office record', $this->backofficeUrl('sh/sh_exhibitions', "1;{$reference->part(0)}"), LegacyLinkConfidence::EXACT, $reference->raw),
                ]
                : [],
            'sh_exhibition_themes' => $this->resolveThemeCollection($collection, $reference, $legacyLanguage),
            default => [],
        };
    }

    /** @return array<int, LegacyLink> */
    private function resolveThemeCollection(Collection $collection, LegacyReference $reference, string $legacyLanguage): array
    {
        $parentReference = LegacyReference::parse($collection->parent?->backward_compatibility);

        if (! $parentReference?->is('mwnf3_sharing_history', 'sh_exhibitions') || ! $parentReference->hasParts(1)) {
            return [LegacyLink::diagnostic('Sharing History theme page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, 'Theme URLs need the parent exhibition id.')];
        }

        return [
            LegacyLink::public(
                label: 'Sharing History exhibition items',
                url: $this->host('sharing_history')."/exh_items.php?eId={$parentReference->part(0)}&lan={$legacyLanguage}",
                confidence: LegacyLinkConfidence::INFERRED,
                source: $reference->raw,
                note: 'Theme collections resolve to their parent exhibition item list.',
            ),
            LegacyLink::backoffice(
                label: 'Sharing History exhibition theme back-office record',
                url: $this->backofficeUrl('sh/sh_exhibitions', "1;{$parentReference->part(0)};{$reference->part(0)}"),
                confidence: LegacyLinkConfidence::INFERRED,
                source: $reference->raw,
                note: 'Theme collections resolve through their parent exhibition record.',
            ),
        ];
    }

    /** @return array<int, LegacyLink> */
    private function resolvePartner(Partner $partner, LegacyReference $reference): array
    {
        if ($reference->table !== 'sh_partners' || ! $reference->hasParts(1)) {
            return [];
        }

        $country = $this->legacyCountryCode($partner->country);

        if ($country === null) {
            return [LegacyLink::diagnostic('Sharing History partner page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, 'The partner legacy URL needs a country code.')];
        }

        $project = Config::string('legacy.links.sharing_history_project', 'AWE');
        $partnerCode = strtoupper((string) $reference->part(0));

        return [
            LegacyLink::public(
                label: 'Sharing History partner page',
                url: $this->host('sharing_history')."/pm_partner.php?id={$partnerCode};{$country}&shpro={$project}&",
                confidence: LegacyLinkConfidence::INFERRED,
                source: $reference->raw,
                note: 'Country context comes from the Inventory partner.',
            ),
            LegacyLink::backoffice(
                label: 'Sharing History partner back-office record',
                url: $this->backofficeUrl('sh/sh_partners', '1;'.(string) $reference->part(0)),
                confidence: LegacyLinkConfidence::EXACT,
                source: $reference->raw,
            ),
        ];
    }
}
