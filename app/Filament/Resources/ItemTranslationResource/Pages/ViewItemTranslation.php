<?php

namespace App\Filament\Resources\ItemTranslationResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Filament\Resources\ItemTranslationResource;
use App\Filament\Widgets\SiblingTranslationsWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItemTranslation extends ViewRecord
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
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SiblingTranslationsWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'parentId' => $this->record->item_id ?? throw new \RuntimeException('Translation record is missing a required item_id.'),
            'parentType' => 'item',
        ];
    }
}
