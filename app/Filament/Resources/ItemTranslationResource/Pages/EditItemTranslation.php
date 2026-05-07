<?php

namespace App\Filament\Resources\ItemTranslationResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\ItemTranslationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemTranslation extends EditRecord
{
    protected static string $resource = ItemTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentItem')
                ->label('View parent item')
                ->icon('heroicon-o-archive-box')
                ->url(fn (): ?string => $this->record->item
                    ? (auth()->user()?->can('view', $this->record->item)
                        ? ItemResource::getUrl('view', ['record' => $this->record->item])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->record->item !== null
                    && (auth()->user()?->can('view', $this->record->item) ?? false)),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Translation';
    }
}
