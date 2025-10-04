<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $language = Language::factory()->create();

        $response = $this->delete(route('languages.destroy', $language));
        $response->assertRedirect(route('languages.index'));
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }
}
