<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionTranslationResource;
use App\Models\CollectionTranslation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectionTranslation extends ViewRecord
{
    protected static string $resource = CollectionTranslationResource::class;

    private function translationRecord(): CollectionTranslation
    {
        /** @var CollectionTranslation $record */
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
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
