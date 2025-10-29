<?php

namespace Tests\Web\Pages;

use App\Models\ItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ItemTranslationTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'item-translations';
    }

    protected function getModelClass(): string
    {
        return ItemTranslation::class;
    }

    protected function getFormData(): array
    {
        return ItemTranslation::factory()->make()->toArray();
    }

    /**
     * Override to exclude JSON fields that get double-encoded
     */
    protected function getDatabaseAssertions(array $data): array
    {
        return array_diff_key($data, array_flip(['extra', '_token', '_method']));
    }
}
