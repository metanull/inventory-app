<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionTranslationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionTranslation extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = CollectionTranslationResource::class;

    private function translationRecord(): \App\Models\CollectionTranslation
    {
        /** @var \App\Models\CollectionTranslation $record */
        $record = $this->getRecord();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentCollection')
                ->label('View parent collection')
                ->icon('heroicon-o-rectangle-stack')
                ->url(fn (): ?string => $this->translationRecord()->collection
                    ? (auth()->user()?->can('view', $this->translationRecord()->collection)
                        ? CollectionResource::getUrl('view', ['record' => $this->translationRecord()->collection])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->translationRecord()->collection !== null
                    && (auth()->user()?->can('view', $this->translationRecord()->collection) ?? false)),
            DeleteAction::make(),
        ];
    }
}
