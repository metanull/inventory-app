<?php

namespace App\Filament\Resources\PartnerTranslationResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\PartnerResource;
use App\Filament\Resources\PartnerTranslationResource;
use App\Models\PartnerTranslation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerTranslation extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = PartnerTranslationResource::class;

    private function translationRecord(): PartnerTranslation
    {
        /** @var PartnerTranslation $record */
        $record = $this->getRecord();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentPartner')
                ->label('View parent partner')
                ->icon('heroicon-o-user-group')
                ->url(fn (): ?string => $this->translationRecord()->partner
                    ? (auth()->user()?->can('view', $this->translationRecord()->partner)
                        ? PartnerResource::getUrl('view', ['record' => $this->translationRecord()->partner])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->translationRecord()->partner !== null
                    && (auth()->user()?->can('view', $this->translationRecord()->partner) ?? false)),
            DeleteAction::make(),
        ];
    }
}
