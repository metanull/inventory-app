<?php

namespace App\Filament\Concerns;

use Filament\Tables\Columns\TextColumn;

trait HasUuidColumn
{
    protected static function uuidColumn(): TextColumn
    {
        return TextColumn::make('id')
            ->label('UUID')
            ->copyable()
            ->toggleable(isToggledHiddenByDefault: true);
    }
}
