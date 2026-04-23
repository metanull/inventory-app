<?php

namespace App\Filament\Concerns;

use Filament\Tables\Columns\TextColumn;

trait HasBackwardCompatibilityColumn
{
    protected static function backwardCompatibilityColumn(): TextColumn
    {
        return TextColumn::make('backward_compatibility')
            ->label('Legacy code')
            ->searchable()
            ->sortable();
    }
}
