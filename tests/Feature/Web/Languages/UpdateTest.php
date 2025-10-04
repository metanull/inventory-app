<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_persists_changes_and_redirects(): void
    {
        $language = Language::factory()->create([
            'internal_name' => 'Old Lang',
        ]);

        $response = $this->put(route('languages.update', $language), [
            'internal_name' => 'New Lang',
        ]);

        $response->assertRedirect(route('languages.show', $language));
        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => 'New Lang',
        ]);
    }
}
