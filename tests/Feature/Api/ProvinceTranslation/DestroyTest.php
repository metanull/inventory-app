namespace Tests\Feature\Api\ProvinceTranslation;

use App\Enums\Permission;
use App\Models\ProvinceTranslation;
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

    public function test_can_destroy_province_translation(): void
    {
        $provinceTranslation = ProvinceTranslation::factory()->create();

        $response = $this->deleteJson(route('province-translation.destroy', ['province_translation' => $provinceTranslation->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('province_translations', [
            'id' => $provinceTranslation->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_non_existent_province_translation(): void
    {
        $response = $this->deleteJson(route('province-translation.destroy', ['province_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}
