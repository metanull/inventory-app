<?php

namespace App\Services\Web;

use App\Models\Item;
use App\Models\Partner;

class PartnerShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array{sections: array<string, array<string, mixed>>}
     */
    public function build(Partner $partner): array
    {
        $partner->load([
            'country',
            'project',
            'monumentItem',
            'partnerImages' => fn ($query) => $query->orderBy('display_order'),
            'translations.context',
            'translations.language',
        ]);

        return [
            'sections' => [
                'images' => [
                    'images' => $partner->partnerImages->values(),
                ],
                'translations' => [
                    'groups' => $this->translationSectionData->build($partner->translations),
                ],
                'monument' => [
                    'item' => $partner->monumentItem,
                    'options' => Item::query()
                        ->monuments()
                        ->orderBy('internal_name')
                        ->get(),
                ],
                'system' => [
                    'id' => $partner->id,
                    'backwardCompatibilityId' => $partner->backward_compatibility,
                    'createdAt' => $partner->created_at,
                    'updatedAt' => $partner->updated_at,
                ],
            ],
        ];
    }
}
