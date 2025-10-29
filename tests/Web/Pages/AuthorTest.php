<?php

namespace Tests\Web\Pages;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class AuthorTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'authors';
    }

    protected function getModelClass(): string
    {
        return Author::class;
    }

    protected function getFormData(): array
    {
        return Author::factory()->make()->toArray();
    }

    /**
     * Override to trim string values (Laravel's TrimStrings middleware trims form data)
     */
    protected function getDatabaseAssertions(array $data): array
    {
        $assertions = array_diff_key($data, array_flip(['_token', '_method']));

        // Trim all string values
        return array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $assertions);
    }
}
