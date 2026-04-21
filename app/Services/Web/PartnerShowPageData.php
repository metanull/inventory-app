<?php

namespace App\Services\Web;

use App\Models\Item;
use App\Models\Partner;

class PartnerShowPageData
{
    public function __construct(private readonly TranslationSectionData $translationSectionData) {}

    /**
     * @return array<string, mixed>
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
            'partnerImages' => $partner->partnerImages->values(),
            'translationGroups' => $this->translationSectionData->build($partner->translations),
            'monumentOptions' => Item::query()
                ->monuments()
                ->orderBy('internal_name')
                ->get(),
        ];
    }
}
