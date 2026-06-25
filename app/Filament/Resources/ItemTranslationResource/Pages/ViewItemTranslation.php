<?php

namespace App\Filament\Resources\ItemTranslationResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\ItemTranslationResource;
use App\Models\ItemTranslation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItemTranslation extends ViewRecord
{
    protected static string $resource = ItemTranslationResource::class;

    private function translationRecord(): ItemTranslation
    {
        /** @var ItemTranslation $record */
        $record = $this->getRecord();

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentItem')
                ->label('View parent item')
                ->icon('heroicon-o-archive-box')
                ->url(fn (): ?string => $this->translationRecord()->item
                    ? (auth()->user()?->can('view', $this->translationRecord()->item)
                        ? ItemResource::getUrl('view', ['record' => $this->translationRecord()->item])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->translationRecord()->item !== null
                    && (auth()->user()?->can('view', $this->translationRecord()->item) ?? false)),
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
