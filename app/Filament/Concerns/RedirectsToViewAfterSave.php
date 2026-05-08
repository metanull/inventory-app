<?php

namespace App\Filament\Concerns;

trait RedirectsToViewAfterSave
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
