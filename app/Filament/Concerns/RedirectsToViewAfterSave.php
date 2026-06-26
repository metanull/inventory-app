<?php

namespace App\Filament\Concerns;

trait RedirectsToViewAfterSave
{
    protected function getRedirectUrl(): string
    {
        /** @var string $url */
        $url = $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);

        return $url;
    }
}
