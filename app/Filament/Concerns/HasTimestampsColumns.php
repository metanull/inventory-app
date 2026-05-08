<?php

namespace App\Filament\Concerns;

use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;

trait HasTimestampsColumns
{
    /**
     * @return array<int, TextColumn>
     */
    protected static function timestampsColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label('Created')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Updated')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * @return array<int, TextEntry>
     */
    protected static function timestampsInfolistEntries(): array
    {
        return [
            TextEntry::make('created_at')
                ->label('Created')
                ->dateTime(),
            TextEntry::make('updated_at')
                ->label('Updated')
                ->dateTime(),
        ];
    }
}
