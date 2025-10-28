namespace Tests\Feature\Api\ItemTranslation;

use App\Enums\Permission;
use App\Models\ItemTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_delete_item_translation(): void
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->deleteJson(route('item-translation.destroy', ['item_translation' => $translation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('item_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_delete_returns_not_found_for_non_existent_item_translation(): void
    {
        $response = $this->deleteJson(route('item-translation.destroy', ['item_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }

    public function test_delete_removes_only_specified_translation(): void
    {
        $translation1 = ItemTranslation::factory()->create();
        $translation2 = ItemTranslation::factory()->create();

        $response = $this->deleteJson(route('item-translation.destroy', ['item_translation' => $translation1->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('item_translations', [
            'id' => $translation1->id,
        ]);

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation2->id,
        ]);
    }
}
