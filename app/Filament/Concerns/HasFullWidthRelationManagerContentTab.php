<?php

namespace App\Filament\Concerns;

use Filament\Infolists\Infolist;

trait HasFullWidthRelationManagerContentTab
{
    protected function makeInfolist(): Infolist
    {
        return parent::makeInfolist()
            ->columns(1);
    }
}
