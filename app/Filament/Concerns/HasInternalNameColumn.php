<?php

namespace App\Filament\Concerns;

use Filament\Tables\Columns\TextColumn;

trait HasInternalNameColumn
{
    protected static function internalNameColumn(): TextColumn
    {
        return TextColumn::make('internal_name')
            ->searchable()
            ->sortable();
    }
}
