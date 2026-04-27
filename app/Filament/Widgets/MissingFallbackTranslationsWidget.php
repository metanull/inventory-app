<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\PartnerResource;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MissingFallbackTranslationsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public string $ownerType = 'item';

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    public function table(Table $table): Table
    {
        return match ($this->ownerType) {
            'collection' => $this->collectionTable($table),
            'partner' => $this->partnerTable($table),
            default => $this->itemTable($table),
        };
    }

    private function missingFallbackQuery(Builder $query): Builder
    {
        return $query->whereDoesntHave(
            'translations',
            fn (Builder $q): Builder => $q
                ->where('language_id', Language::default()->value('id'))
                ->where('context_id', Context::default()->value('id'))
        );
    }

    private function itemTable(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->missingFallbackQuery(
                Item::query()->select(['id', 'internal_name', 'type', 'backward_compatibility', 'updated_at'])
            ))
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('internal_name')
                    ->label('Item')
                    ->searchable(false)
                    ->sortable()
                    ->url(fn (Item $record): string => ItemResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => $state?->label())
                    ->sortable(false),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy code')
                    ->sortable(false),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Items Missing Fallback Translation')
            ->headerActions($this->typeHeaderActions());
    }

    private function collectionTable(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->missingFallbackQuery(
                Collection::query()->select(['id', 'internal_name', 'type', 'backward_compatibility', 'updated_at'])
            ))
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('internal_name')
                    ->label('Collection')
                    ->searchable(false)
                    ->sortable()
                    ->url(fn (Collection $record): string => CollectionResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(false),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy code')
                    ->sortable(false),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Collections Missing Fallback Translation')
            ->headerActions($this->typeHeaderActions());
    }

    private function partnerTable(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->missingFallbackQuery(
                Partner::query()->select(['id', 'internal_name', 'type', 'backward_compatibility', 'updated_at'])
            ))
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('internal_name')
                    ->label('Partner')
                    ->searchable(false)
                    ->sortable()
                    ->url(fn (Partner $record): string => PartnerResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(false),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy code')
                    ->sortable(false),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Partners Missing Fallback Translation')
            ->headerActions($this->typeHeaderActions());
    }

    /** @return array<Action> */
    private function typeHeaderActions(): array
    {
        return [
            Action::make('showItems')
                ->label('Items')
                ->color($this->ownerType === 'item' ? 'primary' : 'gray')
                ->action(fn () => $this->ownerType = 'item'),
            Action::make('showCollections')
                ->label('Collections')
                ->color($this->ownerType === 'collection' ? 'primary' : 'gray')
                ->action(fn () => $this->ownerType = 'collection'),
            Action::make('showPartners')
                ->label('Partners')
                ->color($this->ownerType === 'partner' ? 'primary' : 'gray')
                ->action(fn () => $this->ownerType = 'partner'),
        ];
    }
}
