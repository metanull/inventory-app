<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\CollectionTranslation;
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

    public function test_destroy_deletes_collection_translation_and_redirects(): void
    {
        $translation = CollectionTranslation::factory()->create([
            'title' => 'Translation to Delete',
        ]);

        $response = $this->delete(route('collection-translations.destroy', $translation));
        $response->assertRedirect(route('collection-translations.index'));

        $this->assertDatabaseMissing('collection_translations', [
            'id' => $translation->id,
            'title' => 'Translation to Delete',
        ]);
    }

    public function test_destroy_handles_nonexistent_translation(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->delete(route('collection-translations.destroy', $fakeId));
        $response->assertNotFound();
    }
}
