<?php

namespace App\Support\LegacyLinks\Rules;

use App\Enums\LegacyLinkConfidence;
use App\Models\Item;
use App\Support\LegacyLinks\LegacyLink;
use App\Support\LegacyLinks\LegacyReference;
use App\Support\LegacyLinks\Rules\Concerns\BuildsLegacyUrls;
use App\Support\LegacyLinks\Rules\Concerns\ResolvesThematicGalleryUrls;
use Illuminate\Database\Eloquent\Model;

class Mwnf3ItemLegacyUrlRule implements LegacyUrlRule
{
    use BuildsLegacyUrls, ResolvesThematicGalleryUrls;

    public function supports(Model $record, LegacyReference $reference): bool
    {
        return $record instanceof Item && $reference->schema === 'mwnf3';
    }

    public function resolve(Model $record, LegacyReference $reference, string $legacyLanguage): array
    {
        if (! $record instanceof Item) {
            return [];
        }

        return match ($reference->table) {
            'objects' => $this->resolveDatabaseItem($record, $reference, 'object', 'database/dba_objects', $legacyLanguage, 4),
            'monuments' => $this->resolveDatabaseItem($record, $reference, 'monument', 'database/dba_monuments', $legacyLanguage, 4),
            'objects_pictures' => $this->resolvePictureParent($record, $reference, 'object', 'database/dba_objects', $legacyLanguage),
            'monuments_pictures' => $this->resolvePictureParent($record, $reference, 'monument', 'database/dba_monuments', $legacyLanguage),
            'monument_details' => [LegacyLink::diagnostic('Legacy monument detail', LegacyLinkConfidence::UNSUPPORTED, $reference->raw, 'Monument details usually appear inside the parent monument page; no direct public URL rule is verified yet.')],
            default => [],
        };
    }

    /**
     * @return array<int, LegacyLink>
     */
    private function resolveDatabaseItem(Item $record, LegacyReference $reference, string $legacyType, string $backofficeSection, string $legacyLanguage, int $requiredParts): array
    {
        if (! $reference->hasParts($requiredParts)) {
            return [];
        }

        [$project, $country, $holder, $number] = array_slice($reference->parts, 0, 4);
        $host = $this->projectHost($project);
        $links = [];

        if ($host === null) {
            $links[] = LegacyLink::diagnostic('Legacy public page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, "No public host is configured for project {$project}.");
        } else {
            $links[] = LegacyLink::public(
                label: "Legacy {$legacyType} page",
                url: "{$host}/database_item.php?id={$legacyType};{$project};{$country};{$holder};{$number};{$legacyLanguage}",
                confidence: LegacyLinkConfidence::EXACT,
                source: $reference->raw,
            );
        }

        $links = array_merge($links, $this->resolveThematicCollectionItemLinks($record, $reference, $legacyType, $legacyLanguage));

        $links[] =
            LegacyLink::backoffice(
                label: "Legacy {$legacyType} back-office record",
                url: $this->backofficeUrl($backofficeSection, "1;{$project};{$country};{$holder};{$number}"),
                confidence: LegacyLinkConfidence::EXACT,
                source: $reference->raw,
            );

        return $links;
    }

    /** @return array<int, LegacyLink> */
    private function resolveThematicCollectionItemLinks(Item $record, LegacyReference $reference, string $legacyType, string $legacyLanguage): array
    {
        if (! in_array($reference->table, ['objects', 'monuments'], true)) {
            return [];
        }

        return $record->attachedToCollections()
            ->with('parent.parent.parent.parent.parent.parent.parent.parent.parent.parent')
            ->get()
            ->map(function ($collection) use ($reference, $legacyType, $legacyLanguage): ?LegacyLink {
                $context = $this->thematicGalleryContextForCollection($collection);

                if ($context === null) {
                    return null;
                }

                $url = $this->thematicGalleryDatabaseItemUrl($collection, $reference, $legacyLanguage);
                $label = $context['surface'] === 'exhibition'
                    ? "Thematic exhibition {$legacyType} page"
                    : "Thematic gallery {$legacyType} page";

                if ($url === null) {
                    return LegacyLink::diagnostic($label, LegacyLinkConfidence::REQUIRES_LOOKUP, $collection->backward_compatibility ?? $reference->raw, "The gallery id {$context['gallery_id']} needs a configured public host or exhibition folder.");
                }

                return LegacyLink::public($label, $url, LegacyLinkConfidence::EXACT, $collection->backward_compatibility ?? $reference->raw);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, LegacyLink>
     */
    private function resolvePictureParent(Item $record, LegacyReference $reference, string $legacyType, string $backofficeSection, string $legacyLanguage): array
    {
        $links = $this->resolveDatabaseItem($record, $reference, $legacyType, $backofficeSection, $legacyLanguage, 5);

        return array_map(
            fn (LegacyLink $link): LegacyLink => new LegacyLink($link->label.' parent', $link->url, $link->type, LegacyLinkConfidence::INFERRED, $reference->raw, 'Picture items resolve to the parent legacy record.'),
            $links,
        );
    }
}
