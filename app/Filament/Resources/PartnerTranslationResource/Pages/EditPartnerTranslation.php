<?php

namespace App\Filament\Resources\PartnerTranslationResource\Pages;

use App\Filament\Resources\PartnerResource;
use App\Filament\Resources\PartnerTranslationResource;
use App\Filament\Widgets\SiblingTranslationsWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerTranslation extends EditRecord
{
    protected static string $resource = PartnerTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewParentPartner')
                ->label('View parent partner')
                ->icon('heroicon-o-user-group')
                ->url(fn (): ?string => $this->record->partner
                    ? (auth()->user()?->can('view', $this->record->partner)
                        ? PartnerResource::getUrl('view', ['record' => $this->record->partner])
                        : null)
                    : null)
                ->visible(fn (): bool => $this->record->partner !== null
                    && (auth()->user()?->can('view', $this->record->partner) ?? false)),
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
            'parentId' => $this->record->partner_id ?? throw new \RuntimeException('Translation record is missing a required partner_id.'),
            'parentType' => 'partner',
        ];
    }
}
