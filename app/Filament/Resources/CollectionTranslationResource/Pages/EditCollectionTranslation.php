<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\CollectionTranslationResource;
use App\Filament\Widgets\SiblingTranslationsWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionTranslation extends EditRecord
{
    protected static string $resource = CollectionTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentCollection')
                ->label('View parent collection')
                ->icon('heroicon-o-rectangle-stack')
                ->url(fn (): ?string => $this->record->collection
                    ? (auth()->user()?->can('view', $this->record->collection)
                        ? CollectionResource::getUrl('view', ['record' => $this->record->collection])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->record->collection !== null
                    && (auth()->user()?->can('view', $this->record->collection) ?? false)),
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SiblingTranslationsWidget::class,
        ];
    }

    protected function getWidgetData(): array
    {
        return [
            'parentId' => $this->record->collection_id ?? '',
            'parentType' => 'collection',
        ];
    }
}
