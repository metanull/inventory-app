<?php

namespace App\Filament\Widgets;

use App\Enums\Permission;
use App\Models\Item;
use App\Filament\Resources\ItemResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentItemsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Item::query()
                    ->select(['id', 'internal_name', 'type', 'backward_compatibility', 'created_at'])
                    ->latest()
                    ->limit(10),
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('internal_name')
                    ->label('Name')
                    ->searchable(false)
                    ->sortable(false)
                    ->url(fn ($record) => ItemResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => $state?->label())
                    ->sortable(false),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy code')
                    ->sortable(false),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(false),
            ])
            ->heading('Recently Created Items');
    }
}
