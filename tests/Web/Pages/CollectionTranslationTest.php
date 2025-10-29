<?php

namespace Tests\Web\Pages;

use App\Models\CollectionTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class CollectionTranslationTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'collection-translations';
    }

    protected function getModelClass(): string
    {
        return CollectionTranslation::class;
    }

    protected function getFormData(): array
    {
        return CollectionTranslation::factory()->make()->toArray();
    }

    /**
     * Override to exclude JSON fields that get double-encoded
     */
    protected function getDatabaseAssertions(array $data): array
    {
        return array_diff_key($data, array_flip(['extra', '_token', '_method']));
    }
}
