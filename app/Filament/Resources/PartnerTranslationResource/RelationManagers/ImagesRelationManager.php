<?php

namespace App\Filament\Resources\PartnerTranslationResource\RelationManagers;

use App\Filament\Resources\RelationManagers\BaseImagesRelationManager;
use App\Models\PartnerTranslationImage;

class ImagesRelationManager extends BaseImagesRelationManager
{
    protected static string $relationship = 'partnerTranslationImages';

    protected function imageModelClass(): string
    {
        return PartnerTranslationImage::class;
    }

    protected function ownerForeignKey(): string
    {
        return 'partner_translation_id';
    }

    protected function ownerRouteParameter(): string
    {
        return 'partnerTranslation';
    }

    protected function imageRouteParameter(): string
    {
        return 'partnerTranslationImage';
    }

    protected function imageViewRouteName(): string
    {
        return 'filament.admin.partner-translation-image.view';
    }

    protected function imageDownloadRouteName(): string
    {
        return 'filament.admin.partner-translation-image.download';
    }
}
