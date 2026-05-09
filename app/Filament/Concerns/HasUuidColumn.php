<?php

namespace App\Filament\Concerns;

use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;

trait HasUuidColumn
{
    protected static function uuidColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label('UUID')
            ->copyable()
            ->searchable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    protected static function uuidInfolistEntry(): TextEntry
    {
        return TextEntry::make('id')
            ->label('UUID')
            ->copyable();
    }
}
