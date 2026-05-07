<?php

namespace App\Support\LegacyLinks\Rules;

use App\Enums\LegacyLinkConfidence;
use App\Models\Item;
use App\Support\LegacyLinks\LegacyLink;
use App\Support\LegacyLinks\LegacyReference;
use App\Support\LegacyLinks\Rules\Concerns\BuildsLegacyUrls;
use Illuminate\Database\Eloquent\Model;

class Mwnf3ItemLegacyUrlRule implements LegacyUrlRule
{
    use BuildsLegacyUrls;

    public function supports(Model $record, LegacyReference $reference): bool
    {
        return $record instanceof Item && $reference->schema === 'mwnf3';
    }

    public function resolve(Model $record, LegacyReference $reference, string $legacyLanguage): array
    {
        return match ($reference->table) {
            'objects' => $this->resolveDatabaseItem($reference, 'object', 'database/dba_objects', $legacyLanguage, 4),
            'monuments' => $this->resolveDatabaseItem($reference, 'monument', 'database/dba_monuments', $legacyLanguage, 4),
            'objects_pictures' => $this->resolvePictureParent($reference, 'object', 'database/dba_objects', $legacyLanguage),
            'monuments_pictures' => $this->resolvePictureParent($reference, 'monument', 'database/dba_monuments', $legacyLanguage),
            'monument_details' => [LegacyLink::diagnostic('Legacy monument detail', LegacyLinkConfidence::UNSUPPORTED, $reference->raw, 'Monument details usually appear inside the parent monument page; no direct public URL rule is verified yet.')],
            default => [],
        };
    }

    /**
     * @return array<int, LegacyLink>
     */
    private function resolveDatabaseItem(LegacyReference $reference, string $legacyType, string $backofficeSection, string $legacyLanguage, int $requiredParts): array
    {
        if (! $reference->hasParts($requiredParts)) {
            return [];
        }

        [$project, $country, $holder, $number] = array_slice($reference->parts, 0, 4);
        $host = $this->projectHost($project);

        if ($host === null) {
            return [LegacyLink::diagnostic('Legacy public page', LegacyLinkConfidence::REQUIRES_LOOKUP, $reference->raw, "No public host is configured for project {$project}.")];
        }

        return [
            LegacyLink::public(
                label: "Legacy {$legacyType} page",
                url: "{$host}/database_item.php?id={$legacyType};{$project};{$country};{$holder};{$number};{$legacyLanguage}",
                confidence: LegacyLinkConfidence::EXACT,
                source: $reference->raw,
            ),
            LegacyLink::backoffice(
                label: "Legacy {$legacyType} back-office record",
                url: $this->backofficeUrl($backofficeSection, "1;{$project};{$country};{$holder};{$number}"),
                confidence: LegacyLinkConfidence::EXACT,
                source: $reference->raw,
            ),
        ];
    }

    /**
     * @return array<int, LegacyLink>
     */
    private function resolvePictureParent(LegacyReference $reference, string $legacyType, string $backofficeSection, string $legacyLanguage): array
    {
        $links = $this->resolveDatabaseItem($reference, $legacyType, $backofficeSection, $legacyLanguage, 5);

        return array_map(
            fn (LegacyLink $link): LegacyLink => new LegacyLink($link->label.' parent', $link->url, $link->type, LegacyLinkConfidence::INFERRED, $reference->raw, 'Picture items resolve to the parent legacy record.'),
            $links,
        );
    }
}
